<?php

namespace App\Services\AiAgents;

use App\Models\WebsiteClient;
use App\Services\AiClient;
use Illuminate\Support\Facades\Http;

/**
 * Sub-agent: workflow artikel SEO (Writer → Media → Auditor → Publisher).
 */
class ArticleAgent
{
    public function __construct(private AiClient $aiClient) {}

    /**
     * Workflow artikel SEO: generate (SEO Writer) → sisip gambar → audit → publish jika lolos (>= 80), revisi 1x.
     */
    public function createArticle(?int $websiteId, string $title, string $content, string $keyword = ''): array
    {
        if (!$websiteId) {
            return ['error' => 'ID website diperlukan.'];
        }

        $website = WebsiteClient::find($websiteId);
        if (!$website) {
            return ['error' => 'Website tidak ditemukan.'];
        }

        if (!$website->wp_username || !$website->wp_app_password) {
            return ['error' => "Website {$website->name} belum dikonfigurasi kredensial WP."];
        }

        $hasAi = $this->aiClient->hasApiKey();

        $logs = [
            ['agent' => 'Orchestrator', 'message' => 'Workflow artikel SEO dimulai', 'status' => 'done'],
        ];

        // 1. Generate draft via SEO Writer sub-agent
        if (empty($content) && $hasAi) {
            $logs[] = ['agent' => 'SEO Writer', 'message' => "Men-generate konten artikel untuk {$website->name}...", 'status' => 'loading'];
            $draft = $this->generateArticleDraft($title, $website, $keyword);
            $title = $draft['title'] ?? $title;
            $logs[] = ['agent' => 'SEO Writer', 'message' => 'Konten artikel selesai di-generate', 'status' => 'done'];
        } else {
            $draft = [
                'title' => $title,
                'meta_description' => '',
                'keywords' => $keyword ? [$keyword] : [],
                'html' => $content,
            ];
            $logs[] = ['agent' => 'SEO Writer', 'message' => 'Menggunakan konten yang sudah disediakan', 'status' => 'done'];
        }

        $html = $draft['html'] ?? '';

        // 2. Sisipkan gambar dari penyedia (picsum)
        $logs[] = ['agent' => 'Media Agent', 'message' => 'Mencari & mengunggah 2 gambar dari picsum.photos ke media WordPress...', 'status' => 'loading'];
        $mediaResult = $this->embedImages($website, $html, 2);
        $html = $mediaResult['html'];
        $logs[] = ['agent' => 'Media Agent', 'message' => count($mediaResult['images']) . ' gambar berhasil disisipkan ke artikel', 'status' => 'done'];

        // 3. Audit konten (rule-based, deterministik)
        $logs[] = ['agent' => 'Content Auditor', 'message' => 'Menjalankan audit SEO konten (judul, meta, H1, H2, gambar, keyword)...', 'status' => 'loading'];
        $audit = $this->auditArticleContent($html, $title, $draft['meta_description'] ?? '', $keyword ?: ($draft['keywords'][0] ?? ''));
        $logs[] = ['agent' => 'Content Auditor', 'message' => 'Audit selesai: skor ' . $audit['score'] . '/100 (' . ($audit['passed'] ? 'LOLOS' : 'BELUM LOLOS') . ')', 'status' => 'done'];

        // 4. Revisi 1x jika audit gagal
        if (!$audit['passed'] && $hasAi) {
            $logs[] = ['agent' => 'SEO Writer', 'message' => 'Artikel belum lolos audit, melakukan revisi dengan feedback...', 'status' => 'loading'];
            $feedback = collect($audit['issues'])->pluck('message')->implode('; ');
            $revision = $this->generateArticleDraft($title, $website, $keyword, $feedback);

            if (isset($revision['html']) && strlen($revision['html']) > 100) {
                $title = $revision['title'] ?? $title;
                $html = $revision['html'];

                $logs[] = ['agent' => 'Media Agent', 'message' => 'Menyisipkan ulang gambar untuk versi revisi...', 'status' => 'loading'];
                $mediaResult = $this->embedImages($website, $html, 2);
                $html = $mediaResult['html'];

                $audit = $this->auditArticleContent($html, $title, $revision['meta_description'] ?? '', $keyword ?: ($revision['keywords'][0] ?? ''));
                $logs[] = ['agent' => 'Content Auditor', 'message' => 'Revisi selesai, audit ulang: skor ' . $audit['score'] . '/100 (' . ($audit['passed'] ? 'LOLOS' : 'BELUM LOLOS') . ')', 'status' => 'done'];
            } else {
                $logs[] = ['agent' => 'SEO Writer', 'message' => 'Revisi gagal di-generate, memakai konten versi awal', 'status' => 'done'];
            }
        }

        // 5. Publish jika lolos, simpan draft jika tidak
        $status = $audit['passed'] ? 'publish' : 'draft';
        $logs[] = ['agent' => 'Publisher', 'message' => 'Mempublikasikan artikel ke WordPress...', 'status' => 'loading'];
        $post = $this->publishWpPost($website, $title, $html, $status);
        $logs[] = ['agent' => 'Publisher', 'message' => $status === 'publish' ? 'Artikel berhasil dipublikasikan' : 'Artikel disimpan sebagai draft', 'status' => 'done'];

        if (!$post) {
            return ['error' => "Gagal posting artikel ke {$website->name}."];
        }

        return [
            'success' => true,
            'website' => $website->name,
            'post_id' => $post['id'] ?? null,
            'post_url' => $post['link'] ?? null,
            'status' => $post['status'] ?? $status,
            'score' => $audit['score'],
            'passed' => $audit['passed'],
            'issues' => $audit['issues'],
            'word_count' => $audit['word_count'],
            'images_embedded' => count($mediaResult['images']),
            'logs' => $logs,
            'message' => "Artikel '{$title}' " . ($audit['passed']
                ? "dipublikasikan di {$website->name} (skor SEO {$audit['score']}/100)."
                : "disimpan sebagai draft di {$website->name} karena skor SEO {$audit['score']}/100 belum lolos (min 80)."),
        ];
    }

    // === Sub-agent: SEO Writer ===

    private function generateArticleDraft(string $topic, WebsiteClient $website, string $keyword = '', string $feedback = ''): array
    {
        $keywordLine = $keyword ? " Fokus keyword utama: \"{$keyword}\"." : '';
        $feedbackLine = $feedback ? " Perbaiki berdasarkan hasil audit sebelumnya: {$feedback}" : '';

        $prompt = "Kamu adalah SEO Writer profesional. Tulis artikel SEO bahasa Indonesia untuk website {$website->name} ({$website->url}) dengan topik: \"{$topic}\".{$keywordLine}{$feedbackLine}
Target 800-1500 kata, format HTML (p, h2, h3, ul, li), tepat 1 tag h1, heading berjenjang. Sertakan meta description 100-160 karakter.
Output STRICT satu objek JSON (tanpa teks lain):
{\"title\": \"judul artikel 30-65 karakter\", \"meta_description\": \"deskripsi\", \"keywords\": [\"kata1\", \"kata2\"], \"html\": \"<h1>...</h1>...\"}";

        try {
            $content = $this->aiClient->chat([
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => 'Buat artikelnya'],
            ], 0.7, 8000);

            $json = $this->extractJson($content);
            if ($json && isset($json['html']) && strlen($json['html']) > 100) {
                return $json;
            }
            // fallback: konten berupa HTML polos
            if ($content && strlen($content) > 100) {
                return ['title' => $topic, 'meta_description' => '', 'keywords' => $keyword ? [$keyword] : [], 'html' => $content];
            }
        } catch (\Exception $e) {
            // fallthrough ke fallback
        }

        // Retry 1x — model reasoning kadang habiskan token sehingga konten kosong
        try {
            $content = $this->aiClient->chat([
                ['role' => 'system', 'content' => $prompt . "\nPENTING: JANGAN tulis analisis/pemikiran apa pun. Langsung output JSON saja."],
                ['role' => 'user', 'content' => 'Buat artikelnya'],
            ], 0.4, 8000);

            $json = $this->extractJson($content);
            if ($json && isset($json['html']) && strlen($json['html']) > 100) {
                return $json;
            }
        } catch (\Exception $e) {
            // fallthrough ke fallback
        }

        return ['title' => $topic, 'meta_description' => '', 'keywords' => $keyword ? [$keyword] : [], 'html' => "<h1>{$topic}</h1><p>Konten gagal digenerate.</p>"];
    }

    private function extractJson(string $content): ?array
    {
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $m)) {
            $json = json_decode($m[1], true);
            if (is_array($json)) {
                return $json;
            }
        }
        if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
            $json = json_decode($m[0], true);
            if (is_array($json)) {
                return $json;
            }
        }
        return null;
    }

    // === Sub-agent: Media ===

    private function embedImages(WebsiteClient $website, string $html, int $count = 2): array
    {
        $images = [];
        for ($i = 0; $i < $count; $i++) {
            $imageUrl = $this->uploadImage($website, 'wscrm-' . now()->format('YmdHis') . '-' . $i);
            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        foreach ($images as $idx => $url) {
            $img = '<p><img src="' . $url . '" alt="Ilustrasi ' . ($idx + 1) . '" style="max-width:100%;height:auto;"></p>';
            if (preg_match_all('/<\/h2>/', $html, $m, PREG_OFFSET_CAPTURE)) {
                $h2s = $m[0];
                $target = min($idx, count($h2s) - 1);
                $pos = $h2s[$target][1] + strlen($h2s[$target][0]);
                $html = substr($html, 0, $pos) . $img . substr($html, $pos);
            } else {
                $html .= $img;
            }
        }

        return ['html' => $html, 'images' => $images];
    }

    private function uploadImage(WebsiteClient $website, string $seed): ?string
    {
        try {
            $imageData = Http::timeout(20)->get('https://picsum.photos/seed/' . urlencode($seed) . '/800/450')->body();
            if (empty($imageData)) {
                return null;
            }

            $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'attachment; filename="' . $seed . '.jpg"',
            ])
                ->timeout(30)
                ->withBody($imageData, 'image/jpeg')
                ->post(rtrim($website->url, '/') . '/wp-json/wp/v2/media');

            if ($response->successful()) {
                $media = $response->json();
                return $media['source_url'] ?? ($media['guid']['rendered'] ?? null);
            }
        } catch (\Exception $e) {
            // biarkan kosong
        }

        return null;
    }

    // === Sub-agent: Content Auditor ===

    private function auditArticleContent(string $html, string $title, string $metaDescription, string $keyword): array
    {
        $issues = [];
        $score = 100;

        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
        $wordCount = count(preg_split('/\s+/', $text));

        // Title
        $titleLen = mb_strlen($title);
        if ($titleLen < 30) {
            $issues[] = ['type' => 'warning', 'message' => "Judul terlalu pendek ({$titleLen} karakter, min 30)"];
            $score -= 10;
        } elseif ($titleLen > 65) {
            $issues[] = ['type' => 'warning', 'message' => "Judul terlalu panjang ({$titleLen} karakter, maks 65)"];
            $score -= 10;
        }

        // Meta description
        $metaLen = mb_strlen($metaDescription);
        if ($metaLen < 100) {
            $issues[] = ['type' => 'warning', 'message' => "Meta description terlalu pendek ({$metaLen} karakter, min 100)"];
            $score -= 15;
        }

        // H1
        preg_match_all('/<h1[^>]*>/i', $html, $h1);
        if (count($h1[0]) !== 1) {
            $issues[] = ['type' => 'error', 'message' => 'Harus tepat 1 tag H1'];
            $score -= 15;
        }

        // H2
        preg_match_all('/<h2[^>]*>/i', $html, $h2);
        if (count($h2[0]) < 3) {
            $issues[] = ['type' => 'warning', 'message' => 'Minimal 3 subjudul (H2)'];
            $score -= 10;
        }

        // Gambar + alt
        preg_match_all('/<img[^>]+>/i', $html, $imgs);
        $imgCount = count($imgs[0]);
        if ($imgCount < 2) {
            $issues[] = ['type' => 'warning', 'message' => "Minimal 2 gambar ({$imgCount} terpasang)"];
            $score -= 15;
        }
        preg_match_all('/<img[^>]+alt="[^"]+"[^>]*>/i', $html, $alt);
        if ($imgCount > 0 && count($alt[0]) !== $imgCount) {
            $issues[] = ['type' => 'warning', 'message' => 'Semua gambar wajib punya alt text'];
            $score -= 10;
        }

        // Panjang artikel
        if ($wordCount < 700) {
            $issues[] = ['type' => 'warning', 'message' => "Artikel terlalu pendek ({$wordCount} kata, min 700)"];
            $score -= 15;
        } elseif ($wordCount > 1600) {
            $issues[] = ['type' => 'warning', 'message' => "Artikel terlalu panjang ({$wordCount} kata)"];
            $score -= 5;
        }

        // Keyword density
        if ($keyword) {
            $kwCount = substr_count(mb_strtolower($text), mb_strtolower($keyword));
            if ($kwCount < 3) {
                $issues[] = ['type' => 'warning', 'message' => "Keyword \"{$keyword}\" hanya muncul {$kwCount}x (min 3x)"];
                $score -= 10;
            }
        }

        $score = max(0, $score);

        return [
            'score' => $score,
            'passed' => $score >= 80,
            'issues' => $issues,
            'word_count' => $wordCount,
            'image_count' => $imgCount,
        ];
    }

    // === Sub-agent: Publisher ===

    private function publishWpPost(WebsiteClient $website, string $title, string $html, string $status): ?array
    {
        try {
            $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
            $base = rtrim($website->url, '/') . '/wp-json/wp/v2';

            $payload = [
                'title' => $title,
                'content' => $html,
                'status' => $status,
            ];

            // Beri kategori "artikel" jika ada agar tampil di halaman kategori artikel
            $categories = Http::withHeaders(['Authorization' => 'Basic ' . $auth])
                ->timeout(15)
                ->get($base . '/categories', ['slug' => 'artikel', 'per_page' => 1])
                ->json();
            $categoryId = $categories[0]['id'] ?? null;
            if ($categoryId) {
                $payload['categories'] = [$categoryId];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($base . '/posts', $payload);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

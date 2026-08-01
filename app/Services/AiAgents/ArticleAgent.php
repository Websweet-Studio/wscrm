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
     * Workflow artikel SEO bertahap (melapor tiap langkah via $onEvent):
     * judul → konten → gambar inline → featured image → kategori → audit → revisi (bila gagal) → publish.
     */
    public function createArticle(?int $websiteId, string $title, string $content, string $keyword = '', ?callable $onEvent = null): array
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
        $logs = [];

        $this->emit($onEvent, $logs, 'Workflow artikel SEO dimulai', 'done', 'Orchestrator');

        // 1. SEO Writer — judul artikel + kata kunci gambar
        if (empty($content) && $hasAi) {
            $this->emit($onEvent, $logs, "Men-generate judul artikel & kata kunci gambar untuk {$website->name}...", 'loading', 'SEO Writer');
            $titleData = $this->generateTitleWithTags($title, $website, $keyword);
            $title = $titleData['title'];
            $imageTags = $titleData['tags'];
            $this->emit($onEvent, $logs, "Judul artikel: '{$title}'", 'done', 'SEO Writer');
        } else {
            $imageTags = $keyword ? [$keyword] : ['website', 'business'];
            $this->emit($onEvent, $logs, 'Menggunakan judul yang sudah disediakan', 'done', 'SEO Writer');
        }

        // 2. SEO Writer — konten lengkap
        if (empty($content) && $hasAi) {
            $this->emit($onEvent, $logs, 'Men-generate konten artikel 800-1500 kata dengan SEO Writer...', 'loading', 'SEO Writer');
            $draft = $this->generateArticleDraft($title, $website, $keyword);
            $title = $draft['title'] ?? $title;
            $wordCount = $this->countWords($draft['html'] ?? '');
            $this->emit($onEvent, $logs, "Konten selesai di-generate ({$wordCount} kata)", 'done', 'SEO Writer');
        } else {
            $draft = [
                'title' => $title,
                'meta_description' => '',
                'keywords' => $keyword ? [$keyword] : [],
                'html' => $content,
            ];
            $this->emit($onEvent, $logs, 'Menggunakan konten yang sudah disediakan', 'done', 'SEO Writer');
        }

        $html = $draft['html'] ?? '';

        // 3. Media Agent — gambar inline (relevan dengan topik)
        $this->emit($onEvent, $logs, 'Mencari 2 gambar relevan dengan topik (' . implode(', ', $imageTags) . ')...', 'loading', 'Media Agent');
        $mediaResult = $this->embedImages($website, $html, 2, $imageTags);
        $html = $mediaResult['html'];
        $this->emit($onEvent, $logs, count($mediaResult['images']) . ' gambar berhasil disisipkan ke artikel', 'done', 'Media Agent');

        // 4. Media Agent — featured image
        $this->emit($onEvent, $logs, 'Membuat featured image yang relevan dengan topik...', 'loading', 'Media Agent');
        $featuredMedia = $this->uploadMedia($website, 'wscrm-featured-' . now()->format('YmdHis'), $imageTags);
        $featuredMediaId = $featuredMedia['id'] ?? null;
        $this->emit($onEvent, $logs, $featuredMediaId ? 'Featured image berhasil dibuat' : 'Featured image gagal dibuat (dilewati)', 'done', 'Media Agent');

        // 5. Publisher — pilih kategori relevan
        $this->emit($onEvent, $logs, 'Memilih kategori artikel yang relevan...', 'loading', 'Publisher');
        $category = $this->pickCategory($website);
        $categoryId = $category['id'] ?? null;
        $this->emit($onEvent, $logs, $categoryId ? "Kategori dipilih: {$category['name']}" : 'Kategori tidak ditemukan, memakai default', 'done', 'Publisher');

        // 6. Content Auditor — audit SEO
        $this->emit($onEvent, $logs, 'Menjalankan audit SEO konten (judul, meta, H1, H2, gambar, keyword)...', 'loading', 'Content Auditor');
        $audit = $this->auditArticleContent($html, $title, $draft['meta_description'] ?? '', $keyword ?: ($draft['keywords'][0] ?? ''));
        $this->emit($onEvent, $logs, 'Audit selesai: skor ' . $audit['score'] . '/100 (' . ($audit['passed'] ? 'LOLOS' : 'BELUM LOLOS') . ')', 'done', 'Content Auditor');

        // 7. Revisi 1x jika audit gagal
        if (!$audit['passed'] && $hasAi) {
            $this->emit($onEvent, $logs, 'Artikel belum lolos audit, melakukan revisi dengan feedback...', 'loading', 'SEO Writer');
            $feedback = collect($audit['issues'])->pluck('message')->implode('; ');
            $revision = $this->generateArticleDraft($title, $website, $keyword, $feedback);

            if (isset($revision['html']) && strlen($revision['html']) > 100) {
                $title = $revision['title'] ?? $title;
                $html = $revision['html'];

                $this->emit($onEvent, $logs, 'Menyisipkan ulang gambar untuk versi revisi...', 'loading', 'Media Agent');
                $mediaResult = $this->embedImages($website, $html, 2, $imageTags);
                $html = $mediaResult['html'];

                $audit = $this->auditArticleContent($html, $title, $revision['meta_description'] ?? '', $keyword ?: ($revision['keywords'][0] ?? ''));
                $this->emit($onEvent, $logs, 'Revisi selesai, audit ulang: skor ' . $audit['score'] . '/100 (' . ($audit['passed'] ? 'LOLOS' : 'BELUM LOLOS') . ')', 'done', 'Content Auditor');
            } else {
                $this->emit($onEvent, $logs, 'Revisi gagal di-generate, memakai konten versi awal', 'done', 'SEO Writer');
            }
        }

        // 8. Publisher — publish (dengan kategori + featured image)
        $status = $audit['passed'] ? 'publish' : 'draft';
        $this->emit($onEvent, $logs, 'Mempublikasikan artikel ke WordPress...', 'loading', 'Publisher');
        $post = $this->publishWpPost($website, $title, $html, $status, $categoryId, $featuredMediaId);
        $this->emit($onEvent, $logs, $status === 'publish' ? 'Artikel berhasil dipublikasikan' : 'Artikel disimpan sebagai draft', 'done', 'Publisher');

        if (!$post) {
            return ['error' => "Gagal posting artikel ke {$website->name}."];
        }

        return [
            'success' => true,
            'website' => $website->name,
            'title' => $title,
            'post_id' => $post['id'] ?? null,
            'post_url' => $post['link'] ?? null,
            'status' => $post['status'] ?? $status,
            'score' => $audit['score'],
            'passed' => $audit['passed'],
            'issues' => $audit['issues'],
            'word_count' => $audit['word_count'],
            'images_embedded' => count($mediaResult['images']),
            'featured_image' => (bool) $featuredMediaId,
            'category' => $category['name'] ?? null,
            'logs' => $logs,
            'message' => "Artikel '{$title}' " . ($audit['passed']
                ? "dipublikasikan di {$website->name} (skor SEO {$audit['score']}/100)."
                : "disimpan sebagai draft di {$website->name} karena skor SEO {$audit['score']}/100 belum lolos (min 80)."),
        ];
    }

    /**
     * Catat log + kirim event progress real-time ke chat.
     */
    private function emit(?callable $onEvent, array &$logs, string $message, string $status = 'done', string $agent = 'Orchestrator'): void
    {
        $logs[] = ['agent' => $agent, 'message' => $message, 'status' => $status];
        if ($onEvent) {
            $onEvent($message, $status, $agent);
        }
    }

    private function countWords(string $html): int
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));

        return $text === '' ? 0 : count(preg_split('/\s+/', $text));
    }

    // === Sub-agent: SEO Writer ===

    /**
     * Generate judul artikel + kata kunci gambar (Flickr tags, bahasa Inggris) sekaligus.
     * Call AI kecil supaya user dapat feedback cepat & gambar yang relevan.
     */
    private function generateTitleWithTags(string $topic, WebsiteClient $website, string $keyword = ''): array
    {
        $keywordLine = $keyword ? " Fokus keyword: \"{$keyword}\"." : '';

        $prompt = "Kamu adalah SEO Writer profesional. Untuk website {$website->name} tentang topik: \"{$topic}\".{$keywordLine}
Tentukan:
1. SATU judul artikel SEO terbaik (30-65 karakter, menarik, mengandung keyword jika ada).
2. 3-4 kata kunci GAMBAR dalam bahasa INGGRIS (Flickr tags) yang menggambarkan topik artikel, pisahkan koma.
Output STRICT satu objek JSON (tanpa teks lain):
{\"title\": \"judul artikel\", \"tags\": \"tag1,tag2,tag3\"}";

        try {
            $content = $this->aiClient->chat([
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => 'Judul dan tag gambarnya apa?'],
            ], 0.7, 400);

            $json = $this->extractJson($content);
            if ($json && !empty($json['title'])) {
                $tags = array_values(array_filter(array_map('trim', explode(',', $json['tags'] ?? ''))));
                if (empty($tags)) {
                    $tags = ['website', 'business'];
                }

                return [
                    'title' => $json['title'],
                    'tags' => array_slice($tags, 0, 4),
                ];
            }
        } catch (\Exception $e) {
            // fallback ke topik asli
        }

        return ['title' => $topic, 'tags' => ['website', 'business']];
    }

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

    private function embedImages(WebsiteClient $website, string $html, int $count = 2, array $tags = ['website', 'business']): array
    {
        $images = [];
        $alts = [];
        for ($i = 0; $i < $count; $i++) {
            $media = $this->uploadMedia($website, 'wscrm-' . now()->format('YmdHis') . '-' . $i, $tags);
            if ($media && !empty($media['source_url'])) {
                $images[] = $media['source_url'];
                $alts[] = $media['alt'] ?? '';
            }
        }

        foreach ($images as $idx => $url) {
            $altText = htmlspecialchars($alts[$idx] ?? '', ENT_QUOTES);
            $img = '<p><img src="' . $url . '" alt="' . $altText . '" style="max-width:100%;height:auto;"></p>';
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

    /**
     * Cari gambar relevan via Unsplash Search API berdasarkan tags (keyword topik artikel),
     * unduh, lalu unggah ke media WordPress.
     */
    private function uploadMedia(WebsiteClient $website, string $seed, array $tags = ['website', 'business']): ?array
    {
        try {
            $query = implode(' ', array_slice($tags, 0, 4));

            $search = Http::timeout(20)
                ->withHeaders(['Authorization' => 'Client-ID ' . config('services.unsplash.access_key', env('UNSPLASH_ACCESS_KEY', ''))])
                ->get('https://api.unsplash.com/search/photos', [
                    'query' => $query,
                    'per_page' => 3,
                    'orientation' => 'landscape',
                ])
                ->json();

            $results = $search['results'] ?? [];
            if (empty($results)) {
                return null;
            }

            // seed berbeda → slot gambar berbeda (hindari gambar duplikat per artikel)
            $photo = $results[abs(crc32($seed)) % count($results)];

            $imageData = Http::timeout(30)
                ->withOptions(['allow_redirects' => true])
                ->get($photo['urls']['regular'] ?? '')
                ->body();

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

                return [
                    'id' => $media['id'] ?? null,
                    'source_url' => $media['source_url'] ?? ($media['guid']['rendered'] ?? null),
                    'alt' => $photo['alt_description'] ?? null,
                ];
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

    /**
     * Pilih kategori relevan: prefer slug "artikel", fallback kategori pertama selain "uncategorized".
     */
    private function pickCategory(WebsiteClient $website): ?array
    {
        try {
            $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);

            $categories = Http::withHeaders(['Authorization' => 'Basic ' . $auth])
                ->timeout(15)
                ->get(rtrim($website->url, '/') . '/wp-json/wp/v2/categories', ['per_page' => 100])
                ->json();

            foreach ($categories ?: [] as $c) {
                if (($c['slug'] ?? '') === 'artikel') {
                    return ['id' => $c['id'], 'name' => $c['name'] ?? 'Artikel'];
                }
            }
            foreach ($categories ?: [] as $c) {
                if (($c['slug'] ?? '') !== 'uncategorized') {
                    return ['id' => $c['id'], 'name' => $c['name'] ?? $c['slug']];
                }
            }
        } catch (\Exception $e) {
            // biarkan null
        }

        return null;
    }

    private function publishWpPost(WebsiteClient $website, string $title, string $html, string $status, ?int $categoryId = null, ?int $featuredMediaId = null): ?array
    {
        try {
            $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
            $base = rtrim($website->url, '/') . '/wp-json/wp/v2';

            $payload = [
                'title' => $title,
                'content' => $html,
                'status' => $status,
            ];

            if ($categoryId) {
                $payload['categories'] = [$categoryId];
            }
            if ($featuredMediaId) {
                $payload['featured_media'] = $featuredMediaId;
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

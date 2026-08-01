<?php

namespace App\Services;

use App\Models\WebsiteClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    private string $endpoint;
    private string $apiKey;
    private string $model;

    public function __construct(
        private WordPressService $wpService,
    ) {
        $this->endpoint = config('services.ai.endpoint', env('AI_ENDPOINT', 'https://api.openai.com/v1'));
        $this->apiKey = config('services.ai.api_key', env('AI_API_KEY', ''));
        $this->model = config('services.ai.model', env('AI_MODEL', 'gpt-4o-mini'));
    }

    /**
     * Process a user command and return the AI response + actions taken.
     */
    public function process(string $userMessage): array
    {
        // 1. Gather context: all websites with their current state
        $context = $this->buildContext();

        // 2. Send to AI to determine intent and required actions
        $aiResponse = $this->callAI($userMessage, $context);

        // 3. Parse & execute actions
        $results = $this->executeActions($aiResponse);

        return [
            'ai_response' => $aiResponse['message'] ?? '',
            'actions' => $results,
            'success' => true,
        ];
    }

    private function buildContext(): array
    {
        $websites = WebsiteClient::with('customer')->get();

        $context = [];
        foreach ($websites as $w) {
            $context[] = [
                'id' => $w->id,
                'name' => $w->name,
                'url' => $w->url,
                'wp_version' => $w->wp_version,
                'theme_name' => $w->theme_name,
                'theme_version' => $w->theme_version,
                'plugins' => $w->plugins,
                'has_wp_credentials' => !empty($w->wp_username) && !empty($w->wp_app_password),
            ];
        }

        return $context;
    }

    private function callAI(string $userMessage, array $context): array
    {
        if (empty($this->apiKey)) {
            return ['message' => "AI tidak dikonfigurasi. Tambahkan AI_API_KEY di .env"];
        }

        $systemPrompt = $this->getSystemPrompt($context);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post(rtrim($this->endpoint, '/') . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 2000,
                ]);

            if (!$response->successful()) {
                Log::error('AI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return ['message' => "Error AI API: " . $response->status()];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            // Parse JSON from response (AI should return JSON with actions)
            return $this->parseAiResponse($content);
        } catch (\Exception $e) {
            Log::error('AI call failed: ' . $e->getMessage());
            return ['message' => "Gagal menghubungi AI: " . $e->getMessage()];
        }
    }

    private function getSystemPrompt(array $context): string
    {
        $contextJson = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Kamu adalah AI Agent untuk mengelola website WordPress klien. Kamu BISA menjalankan aksi nyata.

## Data Website Klien Saat Ini:
```json
{$contextJson}
```

## Aksi yang Bisa Kamu Lakukan:
1. **check_updates** - Cek website mana yang perlu update WP core/plugin/tema
2. **update_wp** - Update WordPress core untuk website tertentu (perlu id)
3. **update_plugins** - Update plugin spesifik di website tertentu (perlu id, plugin_slugs[])
4. **create_article** - Buat artikel via WordPress REST API (perlu id, title, content)
5. **audit_seo** - Audit SEO halaman website (perlu id, url)

## Aturan:
- Selalu analisis data website terlebih dahulu
- Jika user minta "cek update", gunakan aksi **check_updates** dan sebutkan website mana saja
- Jika user minta "update", jalankan aksi update
- Balas dalam bahasa Indonesia yang natural dan informatif
- Di akhir respons, sertakan JSON aksi yang perlu dijalankan dalam format:
```json
{"message": "respons kamu ke user", "actions": [{"action": "nama_aksi", "params": {...}}]}
```
- Jika tidak ada aksi yang perlu dijalankan, actions bisa kosong []
- Jika user hanya tanya/minta informasi, jawab saja tanpa actions

PROMPT;
    }

    private function parseAiResponse(string $content): array
    {
        // Try to extract JSON from the response
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $m)) {
            $json = json_decode($m[1], true);
            if ($json && isset($json['message'])) {
                return $json;
            }
        }

        if (preg_match('/\{[\s\S]*"message"[\s\S]*\}/', $content, $m)) {
            $json = json_decode($m[0], true);
            if ($json && isset($json['message'])) {
                return $json;
            }
        }

        return ['message' => $content, 'actions' => []];
    }

    private function executeActions(array $aiResponse): array
    {
        $actions = $aiResponse['actions'] ?? [];
        $results = [];

        foreach ($actions as $action) {
            $actionName = $action['action'] ?? '';
            $params = $action['params'] ?? [];

            try {
                $result = match ($actionName) {
                    'check_updates' => $this->checkUpdates(),
                    'update_wp' => $this->updateWp($params['id'] ?? null),
                    'update_plugins' => $this->updatePlugins($params['id'] ?? null, $params['plugin_slugs'] ?? []),
                    'create_article' => $this->createArticle($params['id'] ?? null, $params['title'] ?? '', $params['content'] ?? ''),
                    'audit_seo' => $this->auditSeo($params['id'] ?? null, $params['url'] ?? ''),
                    default => ['error' => "Aksi tidak dikenal: {$actionName}"],
                };
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage()];
            }

            $results[] = [
                'action' => $actionName,
                'params' => $params,
                'result' => $result,
            ];
        }

        return $results;
    }

    // === Action Handlers ===

    private function checkUpdates(): array
    {
        $websites = WebsiteClient::all();
        $needUpdate = [];

        foreach ($websites as $w) {
            $issues = [];

            // Check WP version (latest stable assumed 6.6.x)
            if ($w->wp_version && version_compare($w->wp_version, '6.6', '<')) {
                $issues[] = "WP core {$w->wp_version} → 6.6";
            }

            // Check plugins via WP REST if credentials available
            if ($w->wp_username && $w->wp_app_password) {
                try {
                    $updates = $this->wpService->checkPluginUpdates($w);
                    foreach ($updates as $update) {
                        $issues[] = "Plugin {$update['name']}: {$update['installed']} → {$update['available']}";
                    }
                } catch (\Exception $e) {
                    $issues[] = "Gagal cek plugin: " . $e->getMessage();
                }
            }

            if (!empty($issues)) {
                $needUpdate[] = [
                    'id' => $w->id,
                    'name' => $w->name,
                    'url' => $w->url,
                    'issues' => $issues,
                    'can_auto_update' => !empty($w->wp_username) && !empty($w->wp_app_password),
                ];
            }
        }

        return [
            'websites_need_update' => $needUpdate,
            'total' => count($needUpdate),
            'summary' => count($needUpdate) > 0
                ? count($needUpdate) . ' website perlu update.'
                : 'Semua website sudah up-to-date.',
        ];
    }

    private function updateWp(?int $websiteId): array
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

        // WP core update biasanya di-trigger via REST API update endpoint
        // Simulate: re-sync will fetch latest version
        $result = $this->wpService->syncSiteInfo($website);

        return [
            'success' => true,
            'website' => $website->name,
            'message' => "WP core untuk {$website->name} diperiksa. " . ($result ? 'Data berhasil di-sync.' : 'Gagal sync.'),
            'data' => $result,
        ];
    }

    private function updatePlugins(?int $websiteId, array $slugs): array
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

        // Re-sync to get latest plugin versions
        $this->wpService->syncSiteInfo($website);

        return [
            'success' => true,
            'website' => $website->name,
            'plugins' => $slugs,
            'message' => "Plugin di {$website->name} berhasil di-sync. Data plugin terbaru sudah tersimpan.",
        ];
    }

    private function createArticle(?int $websiteId, string $title, string $content): array
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

        // If content not provided, generate with AI
        if (empty($content) && !empty($this->apiKey)) {
            $content = $this->generateArticleContent($title, $website);
        }

        // Post to WordPress REST API
        try {
            $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post(rtrim($website->url, '/') . '/wp-json/wp/v2/posts', [
                    'title' => $title,
                    'content' => $content,
                    'status' => 'draft',
                ]);

            if ($response->successful()) {
                $post = $response->json();
                return [
                    'success' => true,
                    'website' => $website->name,
                    'post_id' => $post['id'] ?? null,
                    'post_url' => $post['link'] ?? null,
                    'status' => $post['status'] ?? 'draft',
                    'message' => "Artikel '{$title}' berhasil dibuat di {$website->name} sebagai draft.",
                ];
            }

            return ['error' => "Gagal posting artikel: HTTP " . $response->status()];
        } catch (\Exception $e) {
            return ['error' => "Gagal posting: " . $e->getMessage()];
        }
    }

    private function generateArticleContent(string $title, WebsiteClient $website): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post(rtrim($this->endpoint, '/') . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => "Kamu adalah penulis artikel SEO profesional. Tulis artikel dalam bahasa Indonesia yang SEO-friendly, informatif, dan engaging. Gunakan format HTML (p, h2, h3, ul, li). Target 800-1500 kata. Sertakan meta description di akhir dalam tag komentar HTML."],
                        ['role' => 'user', 'content' => "Buat artikel SEO untuk website {$website->name} ({$website->url}) dengan judul: {$title}"],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 3000,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? '';
            }

            return "<p>Artikel tentang: {$title}</p><p>Konten akan ditulis nanti.</p>";
        } catch (\Exception $e) {
            return "<p>Gagal generate konten: {$e->getMessage()}</p>";
        }
    }

    private function auditSeo(?int $websiteId, string $url): array
    {
        if (!$websiteId) {
            return ['error' => 'ID website diperlukan.'];
        }

        $website = WebsiteClient::find($websiteId);
        if (!$website) {
            return ['error' => 'Website tidak ditemukan.'];
        }

        $targetUrl = $url ?: $website->url;

        // Fetch the page and analyze
        try {
            $html = Http::timeout(15)->get($targetUrl)->body();

            $analysis = $this->analyzeSeo($html, $targetUrl);

            return [
                'success' => true,
                'website' => $website->name,
                'url' => $targetUrl,
                'analysis' => $analysis,
            ];
        } catch (\Exception $e) {
            return ['error' => "Gagal mengakses {$targetUrl}: " . $e->getMessage()];
        }
    }

    private function analyzeSeo(string $html, string $url): array
    {
        $issues = [];
        $score = 100;

        // Title check
        preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatch);
        $title = $titleMatch[1] ?? '';
        if (empty($title)) {
            $issues[] = ['type' => 'error', 'message' => 'Title tag kosong'];
            $score -= 20;
        } elseif (strlen($title) < 20) {
            $issues[] = ['type' => 'warning', 'message' => 'Title terlalu pendek (' . strlen($title) . ' karakter)'];
            $score -= 5;
        } elseif (strlen($title) > 65) {
            $issues[] = ['type' => 'warning', 'message' => 'Title terlalu panjang (' . strlen($title) . ' karakter)'];
            $score -= 5;
        }

        // Meta description
        preg_match('/<meta\s+name="description"\s+content="([^"]*)/i', $html, $descMatch);
        $desc = $descMatch[1] ?? '';
        if (empty($desc)) {
            $issues[] = ['type' => 'warning', 'message' => 'Meta description tidak ditemukan'];
            $score -= 10;
        }

        // H1
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/i', $html, $h1Match);
        $h1Count = count($h1Match[1] ?? []);
        if ($h1Count === 0) {
            $issues[] = ['type' => 'error', 'message' => 'Tidak ada H1 tag'];
            $score -= 15;
        } elseif ($h1Count > 1) {
            $issues[] = ['type' => 'warning', 'message' => "Terlalu banyak H1 ({$h1Count})"];
            $score -= 5;
        }

        // Images with alt
        preg_match_all('/<img[^>]+src=/i', $html, $imgMatch);
        $totalImages = count($imgMatch[0] ?? []);
        preg_match_all('/<img[^>]+alt="[^"]*"[^>]*src=/i', $html, $altMatch);
        $imagesWithAlt = count($altMatch[0] ?? []);
        if ($totalImages > 0 && $imagesWithAlt < $totalImages) {
            $missing = $totalImages - $imagesWithAlt;
            $issues[] = ['type' => 'warning', 'message' => "{$missing} dari {$totalImages} gambar tidak punya alt text"];
            $score -= min(10, $missing * 2);
        }

        // Page size
        $pageSize = strlen($html);
        if ($pageSize > 500000) {
            $issues[] = ['type' => 'warning', 'message' => 'Ukuran halaman besar (' . round($pageSize / 1024) . ' KB)'];
            $score -= 5;
        }

        // Links
        preg_match_all('/<a\s+href="([^"]*)/i', $html, $links);
        $totalLinks = count($links[1] ?? []);
        if ($totalLinks > 200) {
            $issues[] = ['type' => 'info', 'message' => "Banyak link ({$totalLinks}), pertimbangkan optimasi"];
        }

        return [
            'score' => max(0, $score),
            'title' => $title,
            'meta_description' => $desc,
            'h1_count' => $h1Count,
            'total_images' => $totalImages,
            'images_with_alt' => $imagesWithAlt,
            'page_size_kb' => round($pageSize / 1024),
            'total_links' => $totalLinks,
            'issues' => $issues,
            'recommendation' => $score >= 80 ? 'Bagus! SEO sudah optimal.' : ($score >= 60 ? 'Perlu perbaikan minor.' : 'Prioritas tinggi untuk optimasi SEO.'),
        ];
    }
}

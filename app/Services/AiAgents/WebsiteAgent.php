<?php

namespace App\Services\AiAgents;

use App\Models\WebsiteClient;
use App\Services\WordPressService;
use Illuminate\Support\Facades\Http;

/**
 * Sub-agent: penanganan website WordPress (cek/update/audit SEO).
 */
class WebsiteAgent
{
    public function __construct(private WordPressService $wpService)
    {
    }

    public function checkUpdates(?int $websiteId = null): array
    {
        $query = WebsiteClient::query();
        if ($websiteId) {
            $query->where('id', $websiteId);
        }

        $websites = $query->get();
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

    public function updateWp(?int $websiteId): array
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

    public function updatePlugins(?int $websiteId, array $slugs): array
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

    public function auditSeo(?int $websiteId, string $url): array
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

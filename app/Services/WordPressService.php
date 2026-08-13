<?php

namespace App\Services;

use App\Models\WebsiteClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordPressService
{
    /**
     * Sync WP info (version, theme, plugins) from the remote WordPress site.
     * Returns the fetched data or null on failure.
     */
    public function syncSiteInfo(WebsiteClient $website): ?array
    {
        if (!$website->wp_username || !$website->wp_app_password) {
            return null;
        }

        $baseUrl = rtrim($website->url, '/') . '/wp-json/wp/v2';

        try {
            $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
            $headers = ['Authorization' => 'Basic ' . $auth];

            // 1. Get WP version from the site info endpoint
            $wpVersion = $this->fetchWpVersion($website, $auth);

            // 2. Get themes
            $themes = $this->fetchThemes($baseUrl, $headers);

            // 3. Get plugins
            $plugins = $this->fetchPlugins($baseUrl, $headers);

            $data = [
                'wp_version' => $wpVersion,
                'theme_name' => $themes['active'] ?? null,
                'theme_version' => $themes['active_version'] ?? null,
                'plugins' => $plugins,
            ];

            // Persist to database
            $website->update($data);

            return $data;
        } catch (\Exception $e) {
            Log::error('WordPress sync failed for ' . $website->name, [
                'url' => $website->url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function fetchWpVersion(WebsiteClient $website, string $auth): ?string
    {
        // Try multiple endpoints to get WP version
        $endpoints = [
            '/wp-json/',
            '/wp-json/wp-site-health/v1/tests/wordpress-version',
            '/wp-json/wp/v2/settings',
        ];

        foreach ($endpoints as $ep) {
            try {
                $resp = Http::withHeaders([
                    'Authorization' => 'Basic ' . $auth,
                ])->timeout(10)->get(rtrim($website->url, '/') . $ep);

                if ($resp->successful()) {
                    $body = $resp->json();

                    // Check for version in different response formats
                    if (isset($body['namespaces'])) {
                        $version = $this->extractVersionFromResponse($resp);
                        if ($version) {
                            return $version;
                        }
                        continue;
                    }
                    if (isset($body['result'])) {
                        return $body['result'] ?? null;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback: parse the generator meta tag from the homepage
        try {
            $resp = Http::timeout(10)->get(rtrim($website->url, '/') . '/');
            if ($resp->successful() && preg_match('/<meta[^>]+name=["\']generator["\'][^>]+content=["\']WordPress\s+([\d.]+)/i', $resp->body(), $m)) {
                return $m[1];
            }
        } catch (\Exception $e) {
            // ignore, return null below
        }

        return null;
    }

    private function extractVersionFromResponse($resp): ?string
    {
        $body = $resp->body();

        // Try to extract from generator meta tag
        if (preg_match('/content=["\']WordPress\s+([\d.]+)/i', $body, $m)) {
            return $m[1];
        }

        return null;
    }

    private function fetchThemes(string $baseUrl, array $headers): array
    {
        try {
            $resp = Http::withHeaders($headers)
                ->timeout(10)
                ->get($baseUrl . '/themes?status=active');

            if ($resp->successful()) {
                $themes = $resp->json();
                if (!empty($themes) && is_array($themes)) {
                    $active = $themes[0];
                    return [
                        'active' => $this->stringify($active['name'] ?? null) ?: ($this->stringify($active['title']['rendered'] ?? null)),
                        'active_version' => $active['version'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch themes: ' . $e->getMessage());
        }

        return ['active' => null, 'active_version' => null];
    }

    private function fetchPlugins(string $baseUrl, array $headers): array
    {
        try {
            $resp = Http::withHeaders($headers)
                ->timeout(10)
                ->get($baseUrl . '/plugins');

            if ($resp->successful()) {
                $plugins = $resp->json();
                if (is_array($plugins)) {
                    return array_map(function ($p) {
                        return [
                            'name' => $this->stringify($p['name'] ?? null) ?: ($this->stringify($p['title']['rendered'] ?? null) ?: 'Unknown'),
                            'version' => $p['version'] ?? null,
                            'active' => $p['status'] === 'active',
                            'slug' => $p['textdomain'] ?? ($p['slug'] ?? null),
                        ];
                    }, $plugins);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch plugins: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Check plugin updates by comparing installed versions vs latest from wp.org.
     */
    public function checkPluginUpdates(WebsiteClient $website): array
    {
        $plugins = $this->fetchLivePlugins($website);

        return $this->checkPluginUpdatesFromData($plugins);
    }

    /**
     * Check theme updates by comparing installed version vs latest from wp.org.
     */
    public function checkThemeUpdates(WebsiteClient $website): array
    {
        $themes = $this->fetchLiveThemes($website);

        return $this->checkThemeUpdatesFromData($themes);
    }

    /**
     * Check if WordPress core has a newer version available on wp.org.
     */
    public function checkCoreUpdates(WebsiteClient $website): ?array
    {
        $installed = $website->wp_version ?: $this->fetchWpVersion($website, base64_encode($website->wp_username . ':' . $website->wp_app_password));
        if (!$installed) {
            return null;
        }

        $installed = $this->normalizeVersion($installed);

        try {
            $resp = Http::acceptJson()->timeout(10)->get('https://api.wordpress.org/core/version-check/1.7/?channel=stable');
            if (!$resp->successful()) {
                return null;
            }

            $latest = null;
            foreach ($resp->json('offers') ?? [] as $offer) {
                $v = $this->normalizeVersion($offer['version'] ?? '');
                if ($v && version_compare($v, $latest ?? '0', '>')) {
                    $latest = $v;
                }
            }
            if (!$latest) {
                return null;
            }

            return [
                'installed' => $installed,
                'latest' => $latest,
                'needs_update' => version_compare($installed, $latest, '<'),
            ];
        } catch (\Exception $e) {
            Log::error('Core update check failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Aggregate check for core, plugin, and theme updates in one call.
     * Returns ['core' => array|null, 'plugins' => array, 'themes' => array] or null on failure.
     */
    public function checkAllUpdates(WebsiteClient $website): ?array
    {
        if (!$website->wp_username || !$website->wp_app_password) {
            return null;
        }

        $baseUrl = rtrim($website->url, '/') . '/wp-json/wp/v2';
        $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
        $headers = ['Authorization' => 'Basic ' . $auth];

        try {
            $pluginsResp = Http::withHeaders($headers)->timeout(10)->get($baseUrl . '/plugins');
            $themesResp = Http::withHeaders($headers)->timeout(10)->get($baseUrl . '/themes?status=active');

            if (!$pluginsResp->successful() || !$themesResp->successful()) {
                return null;
            }

            return [
                'core' => $this->checkCoreUpdates($website),
                'plugins' => $this->checkPluginUpdatesFromData($pluginsResp->json() ?? []),
                'themes' => $this->checkThemeUpdatesFromData($themesResp->json() ?? []),
            ];
        } catch (\Exception $e) {
            Log::error('WordPress update check failed for ' . $website->name, ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function fetchLivePlugins(WebsiteClient $website): array
    {
        if (!$website->wp_username || !$website->wp_app_password) {
            return [];
        }

        $baseUrl = rtrim($website->url, '/') . '/wp-json/wp/v2';
        $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
        $headers = ['Authorization' => 'Basic ' . $auth];

        try {
            $resp = Http::withHeaders($headers)->timeout(10)->get($baseUrl . '/plugins');

            return $resp->successful() ? ($resp->json() ?? []) : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch live plugins: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get the current plugin list straight from the WordPress site.
     * Each item includes the plugin file (e.g. "akismet/akismet.php") needed to delete it.
     */
    public function getLivePlugins(WebsiteClient $website): array
    {
        $plugins = $this->fetchLivePlugins($website);

        return array_map(function ($p) {
            return [
                'plugin' => $p['plugin'] ?? null,
                'name' => $this->stringify($p['name'] ?? null) ?: ($this->stringify($p['title']['rendered'] ?? null) ?: 'Unknown'),
                'version' => $p['version'] ?? null,
                'active' => ($p['status'] ?? '') === 'active',
            ];
        }, $plugins);
    }

    /**
     * Delete a plugin from the WordPress site (deactivate first if needed).
     * Returns ['success' => bool, 'message' => string].
     */
    public function deletePlugin(WebsiteClient $website, string $pluginFile): array
    {
        $baseUrl = rtrim($website->url, '/') . '/wp-json/wp/v2';
        $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
        $headers = ['Authorization' => 'Basic ' . $auth];
        $encodedFile = rawurlencode($pluginFile);

        try {
            // WP REST API forbids deleting an active plugin; deactivate first.
            $statusResp = Http::withHeaders($headers)->timeout(10)->put($baseUrl . '/plugins/' . $encodedFile, ['status' => 'inactive']);
            if ($statusResp->clientError()) {
                return ['success' => false, 'message' => 'Gagal menonaktifkan plugin: ' . $this->extractError($statusResp)];
            }

            $deleteResp = Http::withHeaders($headers)->timeout(15)->delete($baseUrl . '/plugins/' . $encodedFile);
            if (!$deleteResp->successful()) {
                return ['success' => false, 'message' => 'Gagal menghapus plugin: ' . $this->extractError($deleteResp)];
            }

            return ['success' => true, 'message' => 'Plugin berhasil dihapus dari WordPress.'];
        } catch (\Exception $e) {
            Log::error('Plugin delete failed for ' . $website->name, ['plugin' => $pluginFile, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    private function extractError($resp): string
    {
        $body = $resp->json();
        if (is_array($body) && isset($body['message'])) {
            return (string) $body['message'];
        }

        return $resp->body() ?: 'Kode HTTP ' . $resp->status();
    }

    private function fetchLiveThemes(WebsiteClient $website): array
    {
        if (!$website->wp_username || !$website->wp_app_password) {
            return [];
        }

        $baseUrl = rtrim($website->url, '/') . '/wp-json/wp/v2';
        $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
        $headers = ['Authorization' => 'Basic ' . $auth];

        try {
            $resp = Http::withHeaders($headers)->timeout(10)->get($baseUrl . '/themes?status=active');

            return $resp->successful() ? ($resp->json() ?? []) : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch live themes: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Query wp.org plugins/update-check with installed plugin versions,
     * returns only plugins that have an available update.
     */
    private function checkPluginUpdatesFromData(array $plugins): array
    {
        $payload = [];
        $active = [];

        foreach ($plugins as $plugin) {
            $file = $plugin['plugin'] ?? null;
            if (!$file) {
                continue;
            }
            $payload[$file] = [
                'Name' => $plugin['name'] ?? 'Unknown',
                'Version' => $this->normalizeVersion($plugin['version'] ?? '0'),
            ];
            if (($plugin['status'] ?? '') === 'active') {
                $active[] = $file;
            }
        }

        if (empty($payload)) {
            return [];
        }

        try {
            $resp = Http::acceptJson()->timeout(15)->post('https://api.wordpress.org/plugins/update-check/1.1/', [
                'plugins' => $payload,
                'active' => $active,
                'locale' => 'id_ID',
            ]);

            if (!$resp->successful()) {
                return [];
            }

            $updates = [];
            foreach ($resp->json('plugins') ?? [] as $file => $info) {
                if (!empty($info['update']) && isset($info['update']['new_version'])) {
                    $updates[] = [
                        'name' => $payload[$file]['Name'],
                        'slug' => strtok($file, '/') ?: $file,
                        'installed' => $payload[$file]['Version'],
                        'available' => $this->normalizeVersion($info['update']['new_version']),
                    ];
                }
            }

            return $updates;
        } catch (\Exception $e) {
            Log::error('Plugin update check failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Query wp.org themes/update-check with installed theme versions,
     * returns only themes that have an available update.
     */
    private function checkThemeUpdatesFromData(array $themes): array
    {
        $payload = [];
        $active = [];

        foreach ($themes as $theme) {
            $slug = $theme['stylesheet'] ?? null;
            if (!$slug) {
                continue;
            }
            $payload[$slug] = ['Version' => $this->normalizeVersion($theme['version'] ?? '0')];
            $active[] = $slug;
        }

        if (empty($payload)) {
            return [];
        }

        try {
            $resp = Http::acceptJson()->timeout(15)->post('https://api.wordpress.org/themes/update-check/1.1/', [
                'themes' => $payload,
                'active' => $active,
                'locale' => 'id_ID',
            ]);

            if (!$resp->successful()) {
                return [];
            }

            $updates = [];
            foreach ($resp->json('themes') ?? [] as $slug => $info) {
                if (!empty($info['update']) && isset($info['update']['new_version'])) {
                    $updates[] = [
                        'name' => $slug,
                        'slug' => $slug,
                        'installed' => $payload[$slug]['Version'],
                        'available' => $this->normalizeVersion($info['update']['new_version']),
                    ];
                }
            }

            return $updates;
        } catch (\Exception $e) {
            Log::error('Theme update check failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Strip version suffixes (e.g. "6.7.1-extra" → "6.7.1") so version_compare works.
     */
    private function normalizeVersion(string $version): string
    {
        return preg_replace('/[^0-9.].*$/', '', $version) ?: $version;
    }

    /**
     * WP REST API sometimes returns { raw, rendered } objects instead of plain strings.
     */
    private function stringify($value): ?string
    {
        if (is_array($value)) {
            return $value['rendered'] ?? ($value['raw'] ?? null);
        }

        return is_string($value) ? $value : null;
    }
}

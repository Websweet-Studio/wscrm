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
                        return $this->extractVersionFromResponse($resp);
                    }
                    if (isset($body['result'])) {
                        return $body['result'] ?? null;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function extractVersionFromResponse($resp): ?string
    {
        // WP returns version in headers or body
        $headers = $resp->headers();
        // Some WP setups return version via custom header
        foreach ($headers as $key => $val) {
            if (stripos($key, 'x-content') === 0 || stripos($key, 'x-wp') === 0) {
                $headerBody = $resp->body();
                // Try to extract from generator meta
                if (preg_match('/content="WordPress\s+([\d.]+)/i', $headerBody, $m)) {
                    return $m[1];
                }
            }
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
                        'active' => $active['name'] ?? ($active['title']['rendered'] ?? null),
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
                            'name' => $p['name'] ?? ($p['title']['rendered'] ?? 'Unknown'),
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
     * Check if a specific plugin needs updating by comparing installed vs latest.
     */
    public function checkPluginUpdates(WebsiteClient $website): array
    {
        if (!$website->wp_username || !$website->wp_app_password) {
            return [];
        }

        $baseUrl = rtrim($website->url, '/') . '/wp-json/wp/v2';
        $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);
        $headers = ['Authorization' => 'Basic ' . $auth];

        try {
            $resp = Http::withHeaders($headers)
                ->timeout(15)
                ->get($baseUrl . '/plugins');

            if (!$resp->successful()) {
                return [];
            }

            $plugins = $resp->json();
            $updates = [];

            foreach ($plugins as $plugin) {
                // Check if plugin has update capability via wp-site-health or update endpoint
                $currentVersion = $plugin['version'] ?? '0';
                // WP REST API doesn't directly expose update info,
                // but we can check against the installed version in our DB
                $installed = collect($website->plugins)->firstWhere('slug', $plugin['textdomain'] ?? ($plugin['slug'] ?? null));

                if ($installed && isset($installed['version']) && $installed['version'] !== $currentVersion) {
                    $updates[] = [
                        'name' => $plugin['name'] ?? 'Unknown',
                        'slug' => $plugin['textdomain'] ?? '',
                        'installed' => $installed['version'],
                        'available' => $currentVersion,
                    ];
                }
            }

            return $updates;
        } catch (\Exception $e) {
            Log::error('Plugin update check failed: ' . $e->getMessage());
            return [];
        }
    }
}

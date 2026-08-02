<?php

namespace App\Services;

use App\Models\ThirdPartyPlugin;
use App\Models\WebsiteClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Deploy plugin pihak ketiga (zip) ke website WordPress via endpoint REST
 * kustom /wsbase/v1/install-plugin yang ditanam di theme wsbase.
 */
class PluginDeployService
{
    public function deploy(ThirdPartyPlugin $plugin, WebsiteClient $website): array
    {
        if (!$website->wp_username || !$website->wp_app_password) {
            return [
                'success' => false,
                'message' => "Website {$website->name} belum dikonfigurasi kredensial WP.",
            ];
        }

        try {
            $packageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($plugin->file_path);
            $auth = base64_encode($website->wp_username . ':' . $website->wp_app_password);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
            ])
                ->timeout(180)
                ->post(rtrim($website->url, '/') . '/wp-json/wsbase/v1/install-plugin', [
                    'package_url' => $packageUrl,
                    'activate' => true,
                ]);

            if ($response->successful()) {
                $body = $response->json();

                return [
                    'success' => true,
                    'message' => $body['message'] ?? 'Plugin berhasil diinstall di ' . $website->name . '.',
                ];
            }

            // WP_Error dikembalikan dengan kode status non-2xx — ambil message dari body JSON
            $body = $response->json();
            $message = $body['message'] ?? ($body['data']['message'] ?? null);

            return [
                'success' => false,
                'message' => $message ?: 'Gagal menginstall plugin (HTTP ' . $response->status() . ') di ' . $website->name . '.',
            ];
        } catch (\Exception $e) {
            Log::error('Plugin deploy failed', [
                'plugin' => $plugin->slug,
                'website' => $website->name,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke ' . $website->name . ': ' . $e->getMessage(),
            ];
        }
    }
}

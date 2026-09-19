<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Cek ketersediaan domain lewat API RDASH (Basic Auth).
 *
 * Sebelumnya service ini memanggil `X-API-Key` ke endpoint yang salah sehingga
 * SELALU gagal dan jatuh ke tebakan heuristik ("nama panjang = tersedia").
 * Sekarang memakai RdashService; fallback heuristik hanya dipakai bila
 * kredensial belum diisi atau API tidak dapat dihubungi (dan ditandai `fallback = true`).
 */
class DomainAvailabilityService
{
    public function __construct(private readonly RdashService $rdash) {}

    /**
     * Check domain availability using RDASH API
     *
     * @param  string  $domain  - Full domain name (e.g., 'example.com')
     * @return array<string, mixed>
     */
    public function checkAvailability(string $domain): array
    {
        $domain = strtolower(trim($domain));

        if ($domain === '') {
            return [
                'success' => false,
                'available' => false,
                'domain' => '',
                'status' => 'unknown',
                'message' => 'Nama domain kosong',
                'fallback' => false,
            ];
        }

        if (! $this->rdash->isConfigured()) {
            return $this->getFallbackAvailability($domain, 'Kredensial RDASH belum diisi (RDASH_RESELLER_ID/RDASH_API_KEY)');
        }

        try {
            $data = $this->rdash->availability($domain);
            $available = (bool) ($data['available'] ?? false);

            return [
                'success' => true,
                'available' => $available,
                'domain' => $data['name'] ?? $domain,
                'status' => $available ? 'available' : 'taken',
                'message' => $data['message'] ?? null,
                'is_premium' => (bool) ($data['is_premium_name'] ?? false),
                'premium_price' => $data['premium_registration_price'] ?? null,
                'data' => $data,
                'fallback' => false,
            ];
        } catch (\Throwable $e) {
            Log::warning('RDASH API domain check gagal', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackAvailability($domain, 'API RDASH tidak dapat dihubungi');
        }
    }

    /**
     * Fallback availability check when API is not available
     * This provides reasonable defaults for demo purposes
     *
     * @return array<string, mixed>
     */
    private function getFallbackAvailability(string $domain, string $reason = 'API unavailable'): array
    {
        // Simple heuristic: popular domains are likely taken,
        // unusual/long domains are more likely available
        $commonDomains = ['google', 'facebook', 'twitter', 'instagram', 'youtube', 'amazon', 'apple'];
        $domainParts = explode('.', $domain);
        $baseDomain = strtolower($domainParts[0]);

        $isCommonDomain = false;
        foreach ($commonDomains as $common) {
            if (strpos($baseDomain, $common) !== false) {
                $isCommonDomain = true;
                break;
            }
        }

        // Simple scoring: short common domains likely taken, long unique ones likely available
        $isLikelyAvailable = ! $isCommonDomain && (strlen($baseDomain) > 8 || preg_match('/\d+/', $baseDomain));

        return [
            'success' => false,
            'available' => (bool) $isLikelyAvailable,
            'domain' => $domain,
            'status' => $isLikelyAvailable ? 'available' : 'taken',
            'message' => 'Hasil tebakan (fallback): '.$reason,
            'fallback' => true,
        ];
    }

    /**
     * Check multiple domains availability
     *
     * @param  array<int, string>  $domains  - Array of domain names
     * @return array<string, array<string, mixed>>
     */
    public function checkMultipleAvailability(array $domains): array
    {
        $results = [];

        foreach ($domains as $domain) {
            $results[$domain] = $this->checkAvailability($domain);

            // Add small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }

        return $results;
    }

    /**
     * Check availability with suggestions
     *
     * @param  string  $baseDomain  - Base domain without extension (e.g., 'example')
     * @param  array<int, string>  $extensions  - Array of extensions to check (e.g., ['com', 'net', 'org'])
     * @return array<string, array<string, mixed>>
     */
    public function checkWithSuggestions(string $baseDomain, array $extensions = ['com', 'net', 'org', 'id', 'co.id']): array
    {
        $domains = [];
        foreach ($extensions as $ext) {
            $domains[] = $baseDomain.'.'.$ext;
        }

        return $this->checkMultipleAvailability($domains);
    }
}

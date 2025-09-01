<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainAvailabilityService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rna.api_key');
        $this->baseUrl = config('services.rna.base_url', 'https://api.rdash.id/v1');
    }

    /**
     * Check domain availability using RNA API
     *
     * @param string $domain - Full domain name (e.g., 'example.com')
     * @return array
     */
    public function checkAvailability(string $domain): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(10)
              ->get($this->baseUrl . '/domains/availability', [
                  'domain' => $domain
              ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'available' => $data['available'] ?? false,
                    'domain' => $domain,
                    'status' => $data['status'] ?? 'unknown',
                    'message' => $data['message'] ?? null,
                    'data' => $data
                ];
            } else {
                Log::warning('RNA API domain check failed', [
                    'domain' => $domain,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                // Use fallback when API fails
                return $this->getFallbackAvailability($domain);
            }

        } catch (\Exception $e) {
            Log::error('RNA API domain check exception', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);

            // Fallback: basic heuristic for demo purposes
            return $this->getFallbackAvailability($domain);
        }
    }

    /**
     * Fallback availability check when API is not available
     * This provides reasonable defaults for demo purposes
     */
    private function getFallbackAvailability(string $domain): array
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
        $isLikelyAvailable = !$isCommonDomain && (strlen($baseDomain) > 8 || preg_match('/\d+/', $baseDomain));
        
        return [
            'success' => true,
            'available' => $isLikelyAvailable,
            'domain' => $domain,
            'status' => $isLikelyAvailable ? 'available' : 'taken',
            'message' => 'Availability check using fallback method (API unavailable)',
            'fallback' => true
        ];
    }

    /**
     * Check multiple domains availability
     *
     * @param array $domains - Array of domain names
     * @return array
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
     * @param string $baseDomain - Base domain without extension (e.g., 'example')
     * @param array $extensions - Array of extensions to check (e.g., ['com', 'net', 'org'])
     * @return array
     */
    public function checkWithSuggestions(string $baseDomain, array $extensions = ['com', 'net', 'org', 'id', 'co.id']): array
    {
        $domains = [];
        foreach ($extensions as $ext) {
            $domains[] = $baseDomain . '.' . $ext;
        }

        return $this->checkMultipleAvailability($domains);
    }
}
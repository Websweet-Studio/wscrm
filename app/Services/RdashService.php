<?php

namespace App\Services;

use App\Exceptions\RdashException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien API RDASH (Domain Reseller Open API v1.5).
 *
 * Autentikasi: Basic Auth `resellerId:apiKey` (BUKAN Bearer / X-API-Key).
 * Base URL  : https://api.rdash.id/v1
 * Docs      : https://docs.rdash.id/developer/api · Swagger: https://api.rdash.id/swagger
 *
 * Kredensial diambil dari config('services.rdash.*'):
 *   RDASH_RESELLER_ID  (panel RDash → Settings → reseller ID, tidak bisa diubah)
 *   RDASH_API_KEY      (generate di panel; IP server wajib di-whitelist)
 */
class RdashService
{
    private ?string $resellerId;

    private ?string $apiKey;

    private string $baseUrl;

    private int $timeout;

    public function __construct(?array $config = null)
    {
        $config ??= (array) config('services.rdash', []);

        $this->resellerId = ($config['reseller_id'] ?? null) !== null ? (string) $config['reseller_id'] : null;
        $this->apiKey = ($config['api_key'] ?? null) !== null ? (string) $config['api_key'] : null;
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'https://api.rdash.id/v1'), '/');
        $this->timeout = (int) ($config['timeout'] ?? 25);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->resellerId) && ! empty($this->apiKey);
    }

    public function assertConfigured(): void
    {
        if ($this->isConfigured()) {
            return;
        }

        throw new RdashException(
            'Kredensial RDASH belum diisi. Set RDASH_RESELLER_ID dan RDASH_API_KEY di .env, '
            .'lalu jalankan `php artisan config:clear`. Reseller ID ada di panel RDash → Settings.'
        );
    }

    private function client(): PendingRequest
    {
        $this->assertConfigured();

        return Http::withBasicAuth((string) $this->resellerId, (string) $this->apiKey)
            ->baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout($this->timeout);
    }

    /**
     * Panggil endpoint RDASH. Melempar RdashException untuk error HTTP/transport.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    private function call(string $method, string $path, array $query = [], array $form = []): array
    {
        try {
            $request = $this->client();
            $response = match (strtoupper($method)) {
                'GET' => $request->get($path, $query),
                'POST' => $request->asForm()->post($path, $form),
                'PUT' => $request->asForm()->put($path, $form),
                'DELETE' => $request->delete($path, $query),
                default => throw new RdashException("Metode HTTP tidak didukung: {$method}"),
            };
        } catch (RdashException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RdashException('Gagal menghubungi API RDASH: '.$e->getMessage());
        }

        $body = $response->json();
        $body = is_array($body) ? $body : ['raw' => $response->body()];

        if ($response->failed()) {
            $message = $body['message'] ?? null;
            if (! is_string($message) || $message === '') {
                $message = 'HTTP '.$response->status();
            }

            if ($response->status() === 401 || $response->status() === 422) {
                $message .= ' — periksa RDASH_RESELLER_ID/RDASH_API_KEY dan pastikan IP server sudah di-whitelist di panel RDash.';
            }

            Log::warning('RDASH API gagal', [
                'method' => $method,
                'path' => $path,
                'status' => $response->status(),
                'message' => $body['message'] ?? null,
            ]);

            throw new RdashException($message, $response->status(), $response->body());
        }

        return $body;
    }

    /** @return array<int, array<string, mixed>> halaman data mentah */
    private function page(string $path, array $query, int $page, int $perPage): array
    {
        $query['page'] = $page;
        $query['limit'] = $perPage;

        $body = $this->call('GET', $path, $query);
        $data = $body['data'] ?? [];

        return is_array($data) ? array_values($data) : [];
    }

    /**
     * Ambil semua halaman dari endpoint berpaginasi.
     *
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    private function collect(string $path, array $query = [], int $perPage = 100, int $maxPages = 50): array
    {
        $rows = [];
        for ($page = 1; $page <= $maxPages; $page++) {
            $chunk = $this->page($path, $query, $page, $perPage);
            $rows = array_merge($rows, $chunk);

            if (count($chunk) < $perPage) {
                break;
            }
        }

        return $rows;
    }

    /** Profil reseller (dipakai juga sebagai uji kredensial). @return array<string, mixed> */
    public function profile(): array
    {
        return (array) ($this->call('GET', '/account/profile')['data'] ?? []);
    }

    /** Saldo akun reseller. @return array<string, mixed> */
    public function balance(): array
    {
        return (array) ($this->call('GET', '/account/balance')['data'] ?? []);
    }

    /**
     * Daftar domain milik reseller.
     *
     * @param  array<string, mixed>  $filter  customer_id, name, status, verification_status, required_document, expired_range, created_range
     * @return array<int, array<string, mixed>>
     */
    public function domains(array $filter = [], int $perPage = 100, int $maxPages = 50): array
    {
        return $this->collect('/domains', $filter, $perPage, $maxPages);
    }

    /** Domain by nama persis. @return array<string, mixed>|null */
    public function domainByName(string $name): ?array
    {
        $rows = $this->domains(['name' => $name], 10, 2);

        foreach ($rows as $row) {
            if (strcasecmp((string) ($row['name'] ?? ''), $name) === 0) {
                return $row;
            }
        }

        return null;
    }

    /** Detail domain (termasuk kontak/customer). @return array<string, mixed> */
    public function domainDetail(int $domainId): array
    {
        return (array) ($this->call('GET', "/domains/{$domainId}")['data'] ?? []);
    }

    /** Cek ketersediaan domain. `available` = integer 0/1. @return array<string, mixed> */
    public function availability(string $domain, bool $includePremium = false): array
    {
        return (array) ($this->call('GET', '/domains/availability', [
            'domain' => $domain,
            'include_premium_domains' => $includePremium,
        ])['data'] ?? []);
    }

    /**
     * Daftar harga produk (harga modal/reseller).
     *
     * @return array<int, array<string, mixed>>
     */
    public function prices(?string $extension = null, int $perPage = 100, int $maxPages = 50): array
    {
        $query = [];
        if ($extension !== null && $extension !== '') {
            $query['domainExtension[extension]'] = $extension;
        }

        return $this->collect('/account/prices', $query, $perPage, $maxPages);
    }

    /**
     * Perpanjang domain.
     *
     * @param  string  $currentDate  tanggal expired saat ini di RDash (Y-m-d)
     * @return array<string, mixed>
     */
    public function renew(int $domainId, int $period, string $currentDate, bool $buyWhoisProtection = false): array
    {
        return (array) ($this->call('POST', "/domains/{$domainId}/renew", [], [
            'period' => $period,
            'current_date' => $currentDate,
            'buy_whois_protection' => $buyWhoisProtection ? 1 : 0,
        ])['data'] ?? []);
    }

    /**
     * Notifikasi polling (hasil asinkron: registrasi/transfer/renew).
     *
     * @return array<int, array<string, mixed>>
     */
    public function polls(?int $actionStatus = null, int $perPage = 100): array
    {
        $query = $actionStatus === null ? [] : ['action_status' => $actionStatus];

        return $this->collect('/status', $query, $perPage, 5);
    }

    /** Tandai poll sudah ditangani (0 pending, 1 complete). @return array<string, mixed> */
    public function ackPoll(int $pollId, int $actionStatus = 1): array
    {
        return (array) ($this->call('PUT', "/status/{$pollId}", [], ['action_status' => $actionStatus])['data'] ?? []);
    }

    /**
     * Mutasi saldo reseller.
     *
     * @return array<int, array<string, mixed>>
     */
    public function transactions(string $dateFrom, string $dateTo, array $filter = [], int $perPage = 100): array
    {
        $query = array_merge($filter, ['date_range' => $dateFrom.'_'.$dateTo]);

        return $this->collect('/account/transactions', $query, $perPage, 20);
    }

    /** Daftar customer (RDash). @return array<int, array<string, mixed>> */
    public function customers(int $perPage = 100): array
    {
        return $this->collect('/customers', [], $perPage, 20);
    }

    /**
     * Registrasi domain baru (Lapis 3 — belum dipakai otomatis).
     *
     * @param  array<string, mixed>  $payload  name, period, customer_id, nameserver[], ...
     * @return array<string, mixed>
     */
    public function registerDomain(array $payload): array
    {
        return (array) ($this->call('POST', '/domains', [], $payload)['data'] ?? []);
    }

    /**
     * Uji kredensial + ringkasan akun.
     *
     * @return array{ok: bool, message: string, profile: array<string, mixed>, balance: array<string, mixed>, domains: int}
     */
    public function health(): array
    {
        try {
            $profile = $this->profile();
            $balance = $this->balance();
            $domains = count($this->domains([], 100, 1));
        } catch (RdashException $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'profile' => [],
                'balance' => [],
                'domains' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Kredensial RDASH valid.',
            'profile' => $profile,
            'balance' => $balance,
            'domains' => $domains,
        ];
    }
}

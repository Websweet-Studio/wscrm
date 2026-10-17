<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Penghubung WSCRM ⇄ WSD Shield.
 *
 * Dipakai halaman pelanggan "IP Block" (/customer/ip-block):
 *  - blokir()  → IP mana yang sedang diblokir pada domain milik pelanggan,
 *  - ajukanWhitelist() → whitelist sementara yang langsung diterima Shield, tanpa kuota.
 *
 * Catatan penting soal siapa yang memutuskan:
 *  - Domain pelanggan divalidasi DI SINI (WSCRM yang tahu domain siapa milik siapa).
 *  - Masa berlaku & syarat "IP harus sedang diblokir" dijaga DI SHIELD
 *    (handleCustomerWhitelist) supaya portal tidak bisa jadi celah whitelist bebas.
 *
 * Token mesin terpisah dari token agent ada di WSD_SHIELD_CUSTOMER_TOKEN.
 * Semua panggilan gagal-aman: kalau Shield mati, halaman tetap tampil dengan pesan.
 */
class ShieldClient
{
    protected string $base;

    protected string $token;

    protected int $timeout;

    public function __construct()
    {
        $this->base = rtrim((string) config('services.shield.url'), '/');
        $this->token = (string) config('services.shield.token');
        $this->timeout = (int) config('services.shield.timeout', 6);
    }

    public function aktif(): bool
    {
        return $this->base !== '' && $this->token !== '';
    }

    /**
     * Blokir aktif untuk daftar domain pelanggan + (opsional) IP pelanggan saat ini.
     *
     * @param  array<int, string>  $domains
     * @return array{ok: bool, rows: array<int, array<string, mixed>>, whitelist: array<string, array<string, mixed>>, error: ?string}
     */
    public function blokir(array $domains, ?string $ip = null): array
    {
        $kosong = ['ok' => false, 'rows' => [], 'whitelist' => [], 'error' => null];

        if (! $this->aktif()) {
            return $kosong + ['error' => 'Layanan keamanan belum disetel. Hubungi admin.'];
        }

        try {
            $res = Http::withHeaders(['X-Customer-Token' => $this->token])
                ->timeout($this->timeout)
                ->get($this->base.'/api/customer/blocks', array_filter([
                    'domains' => implode(',', array_slice($domains, 0, 50)),
                    'ip' => $ip,
                ]));

            if (! $res->successful()) {
                return $kosong + ['error' => 'Layanan keamanan menjawab HTTP '.$res->status().'.'];
            }

            $data = $res->json() ?: [];

            return [
                'ok' => true,
                'rows' => $data['rows'] ?? [],
                'whitelist' => $data['whitelist'] ?? [],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('ShieldClient::blokir gagal', ['pesan' => $e->getMessage()]);

            return $kosong + ['error' => 'Tidak bisa menghubungi layanan keamanan.'];
        }
    }

    /**
     * Ajukan whitelist sementara — Shield langsung menerimanya (tanpa persetujuan admin).
     *
     * @return array{ok: bool, error: ?string, expires: ?string, hours: ?int, server: ?string, unban: int}
     */
    public function ajukanWhitelist(string $ip, ?string $domain, string $oleh, int $jam, ?string $alasan): array
    {
        $gagal = ['ok' => false, 'error' => null, 'expires' => null, 'hours' => null, 'server' => null, 'unban' => 0];

        if (! $this->aktif()) {
            return $gagal + ['error' => 'Layanan keamanan belum disetel. Hubungi admin.'];
        }

        try {
            $res = Http::withHeaders(['X-Customer-Token' => $this->token])
                ->timeout($this->timeout)
                ->post($this->base.'/api/customer/whitelist', [
                    'ip' => $ip,
                    'domain' => $domain,
                    'by' => $oleh,
                    'hours' => $jam,
                    'reason' => $alasan,
                ]);

            $data = $res->json() ?: [];

            if (! $res->successful() || empty($data['ok'])) {
                return $gagal + ['error' => $data['error'] ?? ('Layanan keamanan menolak (HTTP '.$res->status().').')];
            }

            return [
                'ok' => true,
                'error' => null,
                'expires' => $data['expires'] ?? null,
                'hours' => $data['hours'] ?? $jam,
                'server' => $data['agent'] ?? null,
                'unban' => (int) ($data['unban'] ?? 0),
            ];
        } catch (\Throwable $e) {
            Log::warning('ShieldClient::ajukanWhitelist gagal', ['ip' => $ip, 'pesan' => $e->getMessage()]);

            return $gagal + ['error' => 'Tidak bisa menghubungi layanan keamanan.'];
        }
    }
}

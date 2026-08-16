<?php

namespace App\Services;

use App\Models\DirectAdminSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien API DirectAdmin (Legacy API, port default 2222, auth HTTP Basic).
 *
 * Endpoint yang dipakai:
 * - CMD_API_SELECT_USERS      : daftar username akun
 * - CMD_API_SHOW_USER_CONFIG  : detail akun (email, domain, suspended)
 * - CMD_API_MODIFY_USER       : suspend/unsuspend (action=suspend|unsuspend)
 */
class DirectAdminService
{
    public function settings(): array
    {
        $stored = DirectAdminSetting::allSettings();

        return [
            'scheme' => ($stored['scheme'] ?? '') ?: 'https',
            'host' => $stored['host'] ?? '',
            'port' => ($stored['port'] ?? '') ?: '2222',
            'username' => $stored['username'] ?? '',
            'password' => DirectAdminSetting::getSecret('password', $stored['password'] ?? ''),
            'verify_ssl' => filter_var($stored['verify_ssl'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function isConfigured(): bool
    {
        $s = $this->settings();

        return $s['host'] !== '' && $s['username'] !== '' && $s['password'] !== '';
    }

    /**
     * Kirim request ke API DirectAdmin. Respons urlencoded di-parse ke array,
     * respons JSON juga didukung. Lempar RuntimeException bila HTTP gagal.
     */
    public function request(string $command, array $params = [], string $method = 'GET'): array
    {
        $s = $this->settings();

        if (! $this->isConfigured()) {
            throw new RuntimeException('DirectAdmin belum dikonfigurasi.');
        }

        $url = $s['scheme'].'://'.$s['host'].':'.$s['port'].'/'.$command;

        $http = Http::withBasicAuth($s['username'], $s['password'])
            ->timeout(30)
            ->withHeaders(['Accept' => 'application/json, application/x-www-form-urlencoded']);

        if (! $s['verify_ssl']) {
            $http = $http->withoutVerifying();
        }

        try {
            $response = strtoupper($method) === 'POST'
                ? $http->asForm()->post($url, $params)
                : $http->get($url, $params);

            if ($response->failed()) {
                throw new RuntimeException('DirectAdmin HTTP '.$response->status().': '.$response->body());
            }

            return $this->parseResponse($response->body());
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new RuntimeException('Gagal terhubung ke DirectAdmin: '.$e->getMessage());
        }
    }

    /**
     * Uji koneksi dengan mengambil config user sendiri.
     */
    public function testConnection(): array
    {
        try {
            $result = $this->request('CMD_API_SHOW_USER_CONFIG');

            if ($this->successful($result)) {
                return ['ok' => true, 'message' => $result['text'] ?? 'Koneksi berhasil.'];
            }

            return ['ok' => false, 'message' => $this->failureMessage($result)];
        } catch (RuntimeException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Pesan error dari respons DA yang tidak punya field `text`:
     * tampilkan isi respons asli (HTML/JSON error) agar penyebab gagal terlihat.
     */
    private function failureMessage(array $result): string
    {
        $text = trim((string) ($result['text'] ?? ''));
        if ($text !== '') {
            return $text;
        }

        $raw = trim((string) ($result['_raw'] ?? ''));
        if ($raw !== '') {
            return 'Respons server tidak terduga: '.mb_substr($raw, 0, 300);
        }

        return 'Respons server kosong.';
    }

    /**
     * Daftar akun: username dari SELECT_USERS + detail tiap user dari SHOW_USER_CONFIG.
     */
    public function listAccounts(): array
    {
        $result = $this->request('CMD_API_SELECT_USERS');

        if (! $this->successful($result)) {
            throw new RuntimeException(
                trim((string) ($result['text'] ?? '')) ?: 'Gagal mengambil daftar akun (CMD_API_SELECT_USERS ditolak). Pastikan login berlevel admin/reseller.'
            );
        }

        $usernames = $this->extractList($result);

        $accounts = [];
        foreach ($usernames as $username) {
            $config = $this->request('CMD_API_SHOW_USER_CONFIG', ['user' => $username]);
            $accounts[] = [
                'username' => $username,
                'email' => $config['email'] ?? '',
                'domain' => $config['domain'] ?? '',
                'package' => $config['package'] ?? '',
                'suspended' => strtolower((string) ($config['suspended'] ?? '')) === 'yes',
            ];
        }

        return $accounts;
    }

    public function suspend(string $username): array
    {
        return $this->request('CMD_API_MODIFY_USER', ['action' => 'suspend', 'user' => $username], 'POST');
    }

    public function unsuspend(string $username): array
    {
        return $this->request('CMD_API_MODIFY_USER', ['action' => 'unsuspend', 'user' => $username], 'POST');
    }

    public function successful(array $result): bool
    {
        if (array_key_exists('error', $result)) {
            return (int) $result['error'] === 0;
        }

        // Respons tanpa field `error` — mis. CMD_API_SHOW_USER_CONFIG langsung
        // mengirim dump config user. Anggap sukses bila isi respons valid
        // (tidak kosong dan bukan halaman HTML).
        $raw = trim((string) ($result['_raw'] ?? ''));

        return $raw !== '' && ! $this->looksLikeHtml($raw);
    }

    private function looksLikeHtml(string $body): bool
    {
        $haystack = strtolower($body);

        return str_contains($haystack, '<html') || str_contains($haystack, '<!doctype');
    }

    /**
     * Ambil daftar username dari respons SELECT_USERS yang berupa
     * list[]=user1&list[]=user2 (urlencoded).
     */
    private function extractList(array $result): array
    {
        $list = $result['list'] ?? $result['list[]'] ?? [];

        if (is_string($list)) {
            return [$list];
        }

        return array_values(array_filter(array_map('strval', (array) $list)));
    }

    private function parseResponse(string $body): array
    {
        // Coba parse JSON dulu
        $json = json_decode($body, true);
        if (is_array($json)) {
            $json['_raw'] = $body;

            return $json;
        }

        // Fallback: parse url-encoded legacy response
        $parsed = [];
        parse_str($body, $parsed);
        $parsed['_raw'] = $body;

        return $parsed;
    }
}

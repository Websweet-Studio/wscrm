<?php

namespace App\Console\Commands;

use App\Exceptions\RdashException;
use App\Services\RdashService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RdashCheck extends Command
{
    protected $signature = 'rdash:check {--json : Keluarkan hasil sebagai JSON}';

    protected $description = 'Uji kredensial RDASH (reseller ID + API key) dan tampilkan ringkasan akun';

    public function handle(RdashService $rdash): int
    {
        $configured = $rdash->isConfigured();
        $baseUrl = (string) config('services.rdash.base_url');
        $resellerId = (string) config('services.rdash.reseller_id');
        $keyMasked = $this->mask((string) config('services.rdash.api_key'));
        $ip = $this->publicIp();

        if (! $configured) {
            $message = 'RDASH_RESELLER_ID / RDASH_API_KEY belum diisi di .env';
            if ($this->option('json')) {
                $this->line(json_encode(['ok' => false, 'message' => $message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $this->error($message);
                $this->line('Isi dulu di .env:');
                $this->line('  RDASH_RESELLER_ID=123   # panel RDash → Settings');
                $this->line('  RDASH_API_KEY=xxxxxxxx  # generate di panel, IP server wajib di-whitelist');
                $this->line('  RDASH_BASE_URL='.$baseUrl);
                $this->line('Lalu: php artisan config:clear');
            }

            return self::FAILURE;
        }

        try {
            $health = $rdash->health();
        } catch (RdashException $e) {
            $health = ['ok' => false, 'message' => $e->getMessage(), 'profile' => [], 'balance' => [], 'domains' => 0];
        }

        if ($this->option('json')) {
            $this->line(json_encode($health + [
                'base_url' => $baseUrl,
                'reseller_id' => $resellerId,
                'api_key' => $keyMasked,
                'server_ip' => $ip,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $health['ok'] ? self::SUCCESS : self::FAILURE;
        }

        $this->table(['Kunci', 'Nilai'], [
            ['Base URL', $baseUrl],
            ['Reseller ID', $resellerId ?: '(kosong)'],
            ['API Key', $keyMasked],
            ['IP server ini', $ip ?: '(tidak terdeteksi)'],
        ]);

        if (! $health['ok']) {
            $this->newLine();
            $this->error('GAGAL: '.$health['message']);
            $this->line('Tips: cek reseller ID (panel RDash → Settings) dan pastikan IP di atas sudah masuk whitelist API key.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✓ Kredensial RDASH valid');
        $this->table(['Profil', 'Nilai'], [
            ['ID', $health['profile']['id'] ?? '-'],
            ['Nama', $health['profile']['name'] ?? '-'],
            ['Email', $health['profile']['email'] ?? '-'],
            ['Subdomain', $health['profile']['subdomain'] ?? '-'],
            ['Currency', $health['profile']['currency'] ?? ($health['balance']['currency'] ?? '-')],
            ['Saldo', ($health['balance']['currency'] ?? 'IDR').' '.($health['balance']['balance'] ?? '-')],
            ['Domain terdaftar', $health['domains'].' (halaman pertama, maks 100)'],
        ]);

        return self::SUCCESS;
    }

    private function mask(string $value): string
    {
        if ($value === '') {
            return '(kosong)';
        }

        return str_repeat('*', max(0, strlen($value) - 4)).substr($value, -4);
    }

    private function publicIp(): string
    {
        try {
            return (string) Http::timeout(8)->get('https://api.ipify.org')->body();
        } catch (\Throwable) {
            return '';
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\WebsiteClient;
use App\Services\WordPressService;
use Illuminate\Console\Command;

class CheckWebsiteUpdates extends Command
{
    protected $signature = 'websites:auto-update';

    protected $description = 'Jalankan pengecekan & auto update untuk website dengan scheduler aktif';

    public function handle(WordPressService $wpService): int
    {
        $websites = WebsiteClient::where('auto_update_enabled', true)->get();

        if ($websites->isEmpty()) {
            $this->info('Tidak ada website dengan scheduler aktif.');
            return self::SUCCESS;
        }

        foreach ($websites as $website) {
            $this->info("Memproses {$website->name} ({$website->url})...");

            if (!$website->wp_username || !$website->wp_app_password) {
                $this->record($website, 'Gagal: kredensial WP tidak lengkap');
                $this->error('  ' . $website->last_auto_update_status);
                continue;
            }

            try {
                $data = $wpService->syncSiteInfo($website);

                if ($data === null) {
                    $this->record($website, 'Gagal: tidak dapat sync WordPress');
                    $this->error('  ' . $website->last_auto_update_status);
                    continue;
                }

                $updates = $wpService->checkPluginUpdates($website);
                $status = count($updates) > 0
                    ? 'Berhasil, ' . count($updates) . ' plugin perlu update'
                    : 'Berhasil, semua up-to-date';

                $this->record($website, $status);
                $this->info('  ' . $status);
            } catch (\Exception $e) {
                $this->record($website, 'Gagal: ' . substr($e->getMessage(), 0, 200));
                $this->error('  ' . $website->last_auto_update_status);
            }
        }

        return self::SUCCESS;
    }

    private function record(WebsiteClient $website, string $status): void
    {
        $website->update([
            'last_auto_update_at' => now(),
            'last_auto_update_status' => $status,
        ]);
    }
}

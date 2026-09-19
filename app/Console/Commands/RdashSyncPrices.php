<?php

namespace App\Console\Commands;

use App\Exceptions\RdashException;
use App\Models\DomainPrice;
use App\Services\RdashService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RdashSyncPrices extends Command
{
    protected $signature = 'rdash:sync-prices
        {--apply : Tulis harga modal (base_cost/renewal_cost) ke domain_prices (tanpa ini = dry-run)}
        {--only= : Batasi ke satu ekstensi, contoh: .my.id}
        {--json : Keluarkan hasil sebagai JSON}';

    protected $description = 'Sinkronkan harga modal domain dari API RDASH ke tabel domain_prices (harga jual tidak diubah)';

    public function handle(RdashService $rdash): int
    {
        $apply = (bool) $this->option('apply');
        $only = (string) ($this->option('only') ?? '');

        try {
            $prices = $rdash->prices($only !== '' ? $only : null);
        } catch (RdashException $e) {
            if ($this->option('json')) {
                $this->line(json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_SLASHES));
            } else {
                $this->error($e->getMessage());
            }

            return self::FAILURE;
        }

        $locals = DomainPrice::query()->get()->keyBy(fn (DomainPrice $p) => strtolower((string) $p->extension));

        $changed = [];
        $unchanged = 0;
        $unknown = [];
        $rollback = [];

        foreach ($prices as $item) {
            $extension = (string) ($item['domain_extension']['extension'] ?? '');
            if ($extension === '') {
                continue;
            }
            $base = $this->firstAmount($item['registration'] ?? null);
            $renewal = $this->renewalAmount($item['renewal'] ?? null);

            $local = $locals->get(strtolower($extension));
            if (! $local) {
                $unknown[] = $extension;

                continue;
            }

            $oldBase = (float) $local->base_cost;
            $oldRenewal = (float) $local->renewal_cost;
            $newBase = $base ?? $oldBase;
            $newRenewal = $renewal ?? $oldRenewal;

            $baseDiffers = $base !== null && abs($oldBase - $newBase) >= 0.01;
            $renewalDiffers = $renewal !== null && abs($oldRenewal - $newRenewal) >= 0.01;

            if (! $baseDiffers && ! $renewalDiffers) {
                $unchanged++;

                continue;
            }

            $rollback[] = ['id' => $local->id, 'base_cost' => $oldBase, 'renewal_cost' => $oldRenewal];

            if ($apply) {
                $local->base_cost = $newBase;
                $local->renewal_cost = $newRenewal;
                $local->save();
            }

            $changed[] = [
                'extension' => $extension,
                'old_base' => $oldBase,
                'new_base' => $newBase,
                'old_renewal' => $oldRenewal,
                'new_renewal' => $newRenewal,
                'selling_price' => (float) $local->selling_price,
                'margin' => (float) $local->selling_price - $newBase,
            ];
        }

        $rollbackFile = null;
        if ($apply && $rollback !== []) {
            $rollbackFile = $this->writeRollback($rollback);
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'ok' => true,
                'dry_run' => ! $apply,
                'fetched' => count($prices),
                'changed' => count($changed),
                'unchanged' => $unchanged,
                'unknown' => array_values(array_unique($unknown)),
                'rows' => $changed,
                'rollback_file' => $rollbackFile,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('Sinkronisasi harga domain RDASH — '.($apply ? 'APPLY (perubahan ditulis)' : 'DRY-RUN (tidak ada perubahan ditulis)'));
        $this->table(['Ringkasan', 'Jumlah'], [
            ['Produk harga di RDash', count($prices)],
            ['TLD dengan perubahan harga modal', count($changed)],
            ['TLD sudah sama', $unchanged],
            ['TLD di RDash yang belum ada di domain_prices', count(array_unique($unknown))],
        ]);

        if ($changed !== []) {
            $this->newLine();
            $this->table(
                ['Ekstensi', 'Modal lama', 'Modal baru', 'Renew lama', 'Renew baru', 'Harga jual', 'Margin/domain'],
                array_map(fn (array $r) => [
                    $r['extension'],
                    $this->money($r['old_base']),
                    $this->money($r['new_base']),
                    $this->money($r['old_renewal']),
                    $this->money($r['new_renewal']),
                    $this->money($r['selling_price']),
                    $this->money($r['margin']),
                ], $changed)
            );
        }

        if ($unknown !== []) {
            $this->newLine();
            $this->warn('TLD di RDash yang belum ada di domain_prices (harga jual belum ditetapkan, tidak dibuat otomatis):');
            $this->line('  '.implode(', ', array_unique($unknown)));
        }

        if ($rollbackFile) {
            $this->newLine();
            $this->line('Rollback SQL: storage/app/'.$rollbackFile);
            $this->line('  mysql -u <user> -p <db> < '.(Storage::disk('local')->path($rollbackFile)));
        }

        return self::SUCCESS;
    }

    /** Ambil harga periode 1 tahun dari map {periode: harga}. */
    private function firstAmount(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (! is_array($value) || $value === []) {
            return null;
        }
        if (isset($value['1']) && is_numeric($value['1'])) {
            return (float) $value['1'];
        }
        foreach ($value as $amount) {
            if (is_numeric($amount)) {
                return (float) $amount;
            }
        }

        return null;
    }

    private function renewalAmount(mixed $value): ?float
    {
        return $this->firstAmount($value);
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }

    /** @param  array<int, array<string, mixed>>  $rollback */
    private function writeRollback(array $rollback): string
    {
        $path = 'rdash/rollback-sync-prices-'.date('Ymd-His').'.sql';
        $lines = [
            '-- Rollback harga modal domain_prices oleh rdash:sync-prices',
            '-- Dijalankan: '.date('c'),
            '',
        ];
        foreach ($rollback as $row) {
            $lines[] = sprintf(
                'UPDATE domain_prices SET base_cost = %.2f, renewal_cost = %.2f WHERE id = %d;',
                $row['base_cost'],
                $row['renewal_cost'],
                $row['id']
            );
        }
        $lines[] = '';

        Storage::disk('local')->put($path, implode("\n", $lines));

        return $path;
    }
}

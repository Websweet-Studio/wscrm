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
        {--create-missing : Buat baris baru untuk TLD yang ada di RDash tapi belum ada di domain_prices}
        {--markup=15 : Persen markup dari harga modal untuk harga jual baris baru}
        {--round=5000 : Bulatkan harga jual ke atas ke kelipatan ini}
        {--activate : Baris baru langsung is_active = true (default: nonaktif dulu untuk ditinjau)}
        {--json : Keluarkan hasil sebagai JSON}';

    protected $description = 'Sinkronkan harga modal domain dari API RDASH ke domain_prices (+ opsi buat TLD baru dari RDash)';

    public function handle(RdashService $rdash): int
    {
        $apply = (bool) $this->option('apply');
        $only = (string) ($this->option('only') ?? '');
        $createMissing = (bool) $this->option('create-missing');
        $markup = (float) $this->option('markup');
        $round = max(1.0, (float) $this->option('round'));
        $activate = (bool) $this->option('activate');

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
        $created = [];
        $rollbackUpdates = [];
        $rollbackDeletes = [];

        foreach ($prices as $item) {
            $extension = (string) ($item['domain_extension']['extension'] ?? '');
            if ($extension === '') {
                continue;
            }
            $base = $this->firstAmount($item['registration'] ?? null);
            $renewal = $this->renewalAmount($item['renewal'] ?? null);
            $local = $locals->get(strtolower($extension));

            if (! $local) {
                if (! $createMissing || $base === null) {
                    $unknown[] = $extension;

                    continue;
                }

                $selling = $this->markUp($base, $markup, $round);
                $renewalSell = $this->markUp($renewal ?? $base, $markup, $round);

                $created[] = [
                    'extension' => $extension,
                    'base_cost' => $base,
                    'renewal_cost' => $renewal ?? $base,
                    'selling_price' => $selling,
                    'renewal_price_with_tax' => $renewalSell,
                    'is_active' => $activate,
                    'margin' => $selling - $base,
                ];

                if ($apply) {
                    $row = DomainPrice::create([
                        'extension' => $extension,
                        'base_cost' => $base,
                        'renewal_cost' => $renewal ?? $base,
                        'selling_price' => $selling,
                        'renewal_price_with_tax' => $renewalSell,
                        'is_active' => $activate,
                    ]);
                    $rollbackDeletes[] = $row->id;
                }

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

            $rollbackUpdates[] = ['id' => $local->id, 'base_cost' => $oldBase, 'renewal_cost' => $oldRenewal];

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

        // TLD dengan margin negatif (harga jual di bawah modal) — termasuk baris lama yang tak tersentuh sync.
        $negative = DomainPrice::query()
            ->whereColumn('selling_price', '<', 'base_cost')
            ->orderBy('extension')
            ->get(['extension', 'base_cost', 'selling_price'])
            ->map(fn (DomainPrice $p) => [
                'extension' => $p->extension,
                'base_cost' => (float) $p->base_cost,
                'selling_price' => (float) $p->selling_price,
                'loss' => (float) $p->selling_price - (float) $p->base_cost,
            ])->all();

        $rollbackFile = null;
        if ($apply && ($rollbackUpdates !== [] || $rollbackDeletes !== [])) {
            $rollbackFile = $this->writeRollback($rollbackUpdates, $rollbackDeletes);
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'ok' => true,
                'dry_run' => ! $apply,
                'fetched' => count($prices),
                'changed' => count($changed),
                'created' => count($created),
                'unchanged' => $unchanged,
                'unknown' => array_values(array_unique($unknown)),
                'rows' => $changed,
                'created_rows' => $created,
                'negative_margin' => $negative,
                'rollback_file' => $rollbackFile,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('Sinkronisasi harga domain RDASH — '.($apply ? 'APPLY (perubahan ditulis)' : 'DRY-RUN (tidak ada perubahan ditulis)'));
        $this->table(['Ringkasan', 'Jumlah'], [
            ['Produk harga di RDash', count($prices)],
            ['TLD dengan perubahan harga modal', count($changed)],
            ['TLD baru dibuat', count($created)],
            ['TLD sudah sama', $unchanged],
            ['TLD di RDash yang belum ada di domain_prices', count(array_unique($unknown))],
            ['TLD dengan margin negatif (harga jual < modal)', count($negative)],
        ]);

        if ($created !== []) {
            $this->newLine();
            $this->line('TLD baru (markup '.rtrim(rtrim(number_format($markup, 2, ',', '.'), '0'), ',').'%, dibulatkan ke atas '.$this->money($round).', status '.($activate ? 'AKTIF' : 'NONAKTIF').'):');
            $this->table(
                ['Ekstensi', 'Modal', 'Renew', 'Harga jual', 'Renew + pajak', 'Margin/domain'],
                array_map(fn (array $r) => [
                    $r['extension'],
                    $this->money($r['base_cost']),
                    $this->money($r['renewal_cost']),
                    $this->money($r['selling_price']),
                    $this->money($r['renewal_price_with_tax']),
                    $this->money($r['margin']),
                ], $created)
            );
        }

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

        if ($negative !== []) {
            $this->newLine();
            $this->error('PERHATIAN: harga jual di bawah harga modal RDash — tiap penjualan rugi:');
            $this->table(
                ['Ekstensi', 'Modal', 'Harga jual', 'Rugi/domain'],
                array_map(fn (array $r) => [
                    $r['extension'],
                    $this->money($r['base_cost']),
                    $this->money($r['selling_price']),
                    $this->money($r['loss']),
                ], $negative)
            );
        }

        if ($unknown !== []) {
            $this->newLine();
            $this->warn('TLD di RDash yang belum ada di domain_prices (pakai --create-missing untuk membuatnya):');
            $this->line('  '.implode(', ', array_unique($unknown)));
        }

        if ($rollbackFile) {
            $this->newLine();
            $this->line('Rollback SQL: storage/app/'.$rollbackFile);
            $this->line('  mysql -u <user> -p <db> < '.(Storage::disk('local')->path($rollbackFile)));
        }

        return self::SUCCESS;
    }

    /** Harga jual = modal + markup%, dibulatkan ke atas ke kelipatan $round. */
    private function markUp(float $cost, float $markupPercent, float $round): float
    {
        $target = $cost * (1 + ($markupPercent / 100));

        return ceil($target / $round) * $round;
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

    /**
     * @param  array<int, array<string, mixed>>  $updates
     * @param  array<int, int>  $deletes
     */
    private function writeRollback(array $updates, array $deletes): string
    {
        $path = 'rdash/rollback-sync-prices-'.date('Ymd-His').'.sql';
        $lines = [
            '-- Rollback harga domain_prices oleh rdash:sync-prices',
            '-- Dijalankan: '.date('c'),
            '',
        ];

        foreach ($updates as $row) {
            $lines[] = sprintf(
                'UPDATE domain_prices SET base_cost = %.2f, renewal_cost = %.2f WHERE id = %d;',
                $row['base_cost'],
                $row['renewal_cost'],
                $row['id']
            );
        }

        if ($deletes !== []) {
            $lines[] = '';
            $lines[] = '-- Hapus baris yang dibuat sync ini:';
            $lines[] = 'DELETE FROM domain_prices WHERE id IN ('.implode(', ', $deletes).');';
        }

        $lines[] = '';

        Storage::disk('local')->put($path, implode("\n", $lines));

        return $path;
    }
}

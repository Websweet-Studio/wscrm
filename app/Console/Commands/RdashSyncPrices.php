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
        {--apply : Tulis perubahan ke domain_prices (tanpa ini = dry-run)}
        {--only= : Batasi ke satu ekstensi, contoh: .my.id}
        {--tax=11 : Persen pajak yang ditambahkan ke harga RDash sebelum jadi biaya dasar}
        {--no-tax : Abaikan pajak (biaya dasar = harga RDash apa adanya)}
        {--create-missing : Buat baris baru untuk TLD yang ada di RDash tapi belum ada di domain_prices}
        {--fix-selling : Hitung ulang harga jual untuk TLD yang marginnya di bawah batas --min-margin}
        {--min-margin=0 : Batas margin minimum (% dari modal); 0 = hanya perbaiki yang rugi}
        {--margin=0 : Margin NOMINAL (Rp) per domain, mis. 10000 → harga jual = modal + 10.000 lalu dibulatkan. Bila >0, menggantikan --markup dan menormalkan SEMUA harga jual (naik maupun turun)}
        {--keep= : Ekstensi (dipisah koma) yang harga jualnya DIPERTAHANKAN apa adanya walau marginnya tipis/negatif, contoh: .com,.my.id}
        {--markup=15 : Persen markup dari modal untuk harga jual (baris baru & perbaikan harga)}
        {--round=5000 : Bulatkan harga jual ke atas ke kelipatan ini}
        {--activate : Baris baru langsung is_active = true (default: nonaktif dulu untuk ditinjau)}
        {--json : Keluarkan hasil sebagai JSON}';

    protected $description = 'Sinkronkan harga modal domain dari API RDASH (+ pajak) ke domain_prices, buat TLD baru, dan rapikan harga jual yang rugi';

    public function handle(RdashService $rdash): int
    {
        $apply = (bool) $this->option('apply');
        $only = (string) ($this->option('only') ?? '');
        $createMissing = (bool) $this->option('create-missing');
        $fixSelling = (bool) $this->option('fix-selling');
        $minMargin = (float) $this->option('min-margin');
        $flatMargin = (float) $this->option('margin');
        $keep = $this->parseList((string) ($this->option('keep') ?? ''));

        // Cadangan dari konfigurasi (RDASH_KEEP_SELLING) bila --keep tidak diberikan,
        // supaya sinkronisasi/cron berikutnya tidak diam-diam menaikkan harga jual TLD promo.
        if ($keep === []) {
            $keep = $this->parseList((string) config('services.rdash.keep_selling', ''));
        }

        $markup = (float) $this->option('markup');
        $round = max(1.0, (float) $this->option('round'));
        $activate = (bool) $this->option('activate');
        $taxPercent = $this->option('no-tax') ? 0.0 : (float) $this->option('tax');
        $taxFactor = 1 + ($taxPercent / 100);

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
        $repriced = [];
        $kept = [];
        $rollbackUpdates = [];
        $rollbackDeletes = [];

        foreach ($prices as $item) {
            $extension = (string) ($item['domain_extension']['extension'] ?? '');
            if ($extension === '') {
                continue;
            }

            // Harga RDash masih eksklusif pajak → biaya dasar = harga RDash + pajak.
            $baseRaw = $this->firstAmount($item['registration'] ?? null);
            $renewalRaw = $this->renewalAmount($item['renewal'] ?? null);
            $base = $baseRaw === null ? null : round($baseRaw * $taxFactor, 2);
            $renewal = $renewalRaw === null ? null : round($renewalRaw * $taxFactor, 2);

            $local = $locals->get(strtolower($extension));

            if (! $local) {
                if (! $createMissing || $base === null) {
                    $unknown[] = $extension;

                    continue;
                }

                $selling = $this->priceFor($base, $flatMargin, $markup, $round);
                $renewalSell = $this->priceFor($renewal ?? $base, $flatMargin, $markup, $round);

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

            if ($baseDiffers || $renewalDiffers) {
                $rollbackUpdates[$local->id] = ['id' => $local->id, 'base_cost' => $oldBase, 'renewal_cost' => $oldRenewal];

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
            } else {
                $unchanged++;
            }

            if (! $fixSelling) {
                continue;
            }

            // Perbaiki harga jual yang di bawah modal (atau marginnya di bawah batas --min-margin).
            // Bila --margin=N diberikan, SEMUA harga jual dinormalkan ke modal + N (naik maupun turun).
            $sellNow = (float) $local->selling_price;
            $renewSellNow = (float) $local->renewal_price_with_tax;
            $targetSell = $this->priceFor($newBase, $flatMargin, $markup, $round);
            $targetRenewSell = $this->priceFor($newRenewal, $flatMargin, $markup, $round);

            if ($flatMargin > 0) {
                $needSell = abs($sellNow - $targetSell) >= 0.01;
                $needRenewSell = abs($renewSellNow - $targetRenewSell) >= 0.01;
            } else {
                $needSell = $this->belowMargin($sellNow, $newBase, $minMargin);
                $needRenewSell = $this->belowMargin($renewSellNow, $newRenewal, $minMargin);
            }

            // Harga jual yang dikunci manual (--keep) tidak pernah dihitung ulang
            // walau marginnya tipis/negatif — mis. harga promo TLD tertentu.
            if (($needSell || $needRenewSell) && in_array($this->normExt($extension), $keep, true)) {
                $kept[] = [
                    'extension' => $this->normExt($extension),
                    'base_cost' => $newBase,
                    'selling_price' => $sellNow,
                    'renewal_selling' => $renewSellNow,
                    'margin' => $sellNow - $newBase,
                ];

                continue;
            }

            if (! $needSell && ! $needRenewSell) {
                continue;
            }

            $newSell = $needSell ? $targetSell : $sellNow;
            $newRenewSell = $needRenewSell ? $targetRenewSell : $renewSellNow;

            $rollbackUpdates[$local->id] ??= ['id' => $local->id, 'base_cost' => $oldBase, 'renewal_cost' => $oldRenewal];
            $rollbackUpdates[$local->id]['selling_price'] = $sellNow;
            $rollbackUpdates[$local->id]['renewal_price_with_tax'] = $renewSellNow;

            if ($apply) {
                $local->selling_price = $newSell;
                $local->renewal_price_with_tax = $newRenewSell;
                $local->save();
            }

            $repriced[] = [
                'extension' => $extension,
                'base_cost' => $newBase,
                'old_selling' => $sellNow,
                'new_selling' => $newSell,
                'old_renewal_selling' => $renewSellNow,
                'new_renewal_selling' => $newRenewSell,
                'margin_before' => $sellNow - $newBase,
                'margin_after' => $newSell - $newBase,
            ];
        }

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
            $rollbackFile = $this->writeRollback(array_values($rollbackUpdates), $rollbackDeletes);
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'ok' => true,
                'dry_run' => ! $apply,
                'tax_percent' => $taxPercent,
                'markup_percent' => $markup,
                'margin_flat' => $flatMargin,
                'fetched' => count($prices),
                'changed' => count($changed),
                'created' => count($created),
                'unchanged' => $unchanged,
                'repriced' => count($repriced),
                'kept' => count($kept),
                'unknown' => array_values(array_unique($unknown)),
                'rows' => $changed,
                'created_rows' => $created,
                'repriced_rows' => $repriced,
                'kept_rows' => $kept,
                'negative_margin' => $negative,
                'rollback_file' => $rollbackFile,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $taxNote = $taxPercent > 0 ? ' + pajak '.rtrim(rtrim(number_format($taxPercent, 2, ',', '.'), '0'), ',').'%' : ' (tanpa pajak)';
        $this->info('Sinkronisasi harga domain RDASH — '.($apply ? 'APPLY (perubahan ditulis)' : 'DRY-RUN (tidak ada perubahan ditulis)'));
        $this->line('Biaya dasar = harga RDash'.$taxNote.'.');
        if ($flatMargin > 0) {
            $this->line('Harga jual = modal + '.$this->money($flatMargin).' (margin nominal), dibulatkan ke atas '.$this->money($round).'.');
        }
        $this->table(['Ringkasan', 'Jumlah'], [
            ['Produk harga di RDash', count($prices)],
            ['TLD dengan perubahan biaya dasar', count($changed)],
            ['TLD baru dibuat', count($created)],
            [$flatMargin > 0 ? 'TLD harga jual dinormalkan (margin nominal)' : 'TLD harga jualnya diperbaiki (rugi)', count($repriced)],
            ['TLD harga jual dikunci manual (--keep)', count($kept)],
            ['TLD sudah sama', $unchanged],
            ['TLD di RDash yang belum ada di domain_prices', count(array_unique($unknown))],
            ['TLD dengan margin negatif (harga jual < modal)', count($negative)],
        ]);

        if ($created !== []) {
            $this->newLine();
            $this->line('TLD baru (modal termasuk pajak, markup '.$this->percent($markup).', dibulatkan ke atas '.$this->money($round).', status '.($activate ? 'AKTIF' : 'NONAKTIF').'):');
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

        if ($repriced !== []) {
            $this->newLine();
            $this->line($flatMargin > 0
                ? 'Harga jual dinormalkan (modal + '.$this->money($flatMargin).', bulat ke atas '.$this->money($round).'):'
                : 'Harga jual diperbaiki (markup '.$this->percent($markup).' dari modal termasuk pajak):');
            $this->table(
                ['Ekstensi', 'Modal', 'Jual lama', 'Jual baru', 'Renew lama', 'Renew baru', 'Margin baru'],
                array_map(fn (array $r) => [
                    $r['extension'],
                    $this->money($r['base_cost']),
                    $this->money($r['old_selling']),
                    $this->money($r['new_selling']),
                    $this->money($r['old_renewal_selling']),
                    $this->money($r['new_renewal_selling']),
                    $this->money($r['margin_after']),
                ], $repriced)
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

        if ($kept !== []) {
            $this->newLine();
            $this->warn('Harga jual dikunci manual (--keep) — TIDAK dihitung ulang otomatis:');
            $this->table(
                ['Ekstensi', 'Modal', 'Harga jual', 'Renew + pajak', 'Margin/domain'],
                array_map(fn (array $r) => [
                    $r['extension'],
                    $this->money($r['base_cost']),
                    $this->money($r['selling_price']),
                    $this->money($r['renewal_selling']),
                    $this->money($r['margin']),
                ], $kept)
            );
        }

        if ($negative !== []) {
            $this->newLine();
            $this->error('MASIH RUGI (harga jual < modal): pakai --fix-selling untuk memperbaiki.');
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

    /** Pisahkan opsi daftar "a,b,c" menjadi array ekstensi ternormalisasi (".com"). */
    private function parseList(string $value): array
    {
        $items = array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== '');

        return array_values(array_unique(array_map(fn ($v) => $this->normExt($v), $items)));
    }

    /** Ekstensi selalu bertitik & huruf kecil: "com" → ".com". */
    private function normExt(string $extension): string
    {
        $extension = strtolower(trim($extension));

        return $extension === '' || str_starts_with($extension, '.') ? $extension : '.'.$extension;
    }

    /** Harga jual memakai margin NOMINAL (modal + N, bulat ke atas) atau markup persen. */
    private function priceFor(float $cost, float $flatMargin, float $markupPercent, float $round): float
    {
        return $flatMargin > 0
            ? $this->roundUp($cost + $flatMargin, $round)
            : $this->markUp($cost, $markupPercent, $round);
    }

    /** Bulatkan ke atas ke kelipatan $round (mis. 107.000 → 110.000). */
    private function roundUp(float $value, float $round): float
    {
        return ceil($value / $round) * $round;
    }

    /** Harga jual = modal + markup%, dibulatkan ke atas ke kelipatan $round. */
    private function markUp(float $cost, float $markupPercent, float $round): float
    {
        return $this->roundUp($cost * (1 + ($markupPercent / 100)), $round);
    }

    /** true bila harga jual di bawah modal, atau marginnya di bawah $minMargin%. */
    private function belowMargin(float $selling, float $cost, float $minMargin): bool
    {
        if ($selling < $cost) {
            return true;
        }
        if ($minMargin <= 0 || $cost <= 0) {
            return false;
        }

        return (($selling - $cost) / $cost) * 100 < $minMargin;
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

    private function percent(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',').'%';
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
            $sets = [
                sprintf('base_cost = %.2f', $row['base_cost']),
                sprintf('renewal_cost = %.2f', $row['renewal_cost']),
            ];
            if (isset($row['selling_price'])) {
                $sets[] = sprintf('selling_price = %.2f', $row['selling_price']);
            }
            if (isset($row['renewal_price_with_tax'])) {
                $sets[] = sprintf('renewal_price_with_tax = %.2f', $row['renewal_price_with_tax']);
            }
            $lines[] = sprintf('UPDATE domain_prices SET %s WHERE id = %d;', implode(', ', $sets), $row['id']);
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

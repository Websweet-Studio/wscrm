<?php

namespace App\Console\Commands;

use App\Exceptions\RdashException;
use App\Models\DomainPrice;
use App\Services\RdashService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
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
        {--keep= : Ekstensi (dipisah koma) yang HARGA REGISTRASINYA dipertahankan apa adanya walau marginnya tipis/negatif (harga perpanjangan tetap mengikuti aturan margin), contoh: .com,.my.id}
        {--no-promo : Abaikan harga promo registrasi dari RDash (jangan simpan/tinjau promo_registration)}
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

        $usePromo = ! (bool) $this->option('no-promo');
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
        $promos = [];
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
                $promo = $usePromo ? $this->promoFor($item['promo_registration'] ?? null, $taxFactor, $flatMargin, $markup, $round, $base) : null;

                $created[] = [
                    'extension' => $extension,
                    'base_cost' => $base,
                    'renewal_cost' => $renewal ?? $base,
                    'selling_price' => $selling,
                    'renewal_price_with_tax' => $renewalSell,
                    'is_active' => $activate,
                    'margin' => $selling - $base,
                    'promo_selling_price' => $promo['promo_selling_price'] ?? null,
                ];

                if ($apply) {
                    $row = DomainPrice::create(array_merge([
                        'extension' => $extension,
                        'base_cost' => $base,
                        'renewal_cost' => $renewal ?? $base,
                        'selling_price' => $selling,
                        'renewal_price_with_tax' => $renewalSell,
                        'is_active' => $activate,
                    ], $promo ?? []));

                    if ($promo !== null) {
                        $promos[] = $this->promoRow($row, $promo);
                    }
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

            if ($usePromo) {
                $promoRow = $this->syncPromo($local, $item['promo_registration'] ?? null, $taxFactor, $flatMargin, $markup, $round, $apply, $rollbackUpdates);
                if ($promoRow !== null) {
                    $promos[] = $promoRow;
                }
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

            // --keep hanya mengunci harga REGISTRASI (boleh tipis/negatif, mis. harga promo
            // TLD tertentu). Harga PERPANJANGAN selalu mengikuti aturan margin — permintaan
            // user 19 Sep 2026: perpanjangan .com ikut margin Rp10.000 + bulat Rp5.000.
            if ($needSell && in_array($this->normExt($extension), $keep, true)) {
                $kept[] = [
                    'extension' => $this->normExt($extension),
                    'base_cost' => $newBase,
                    'selling_price' => $sellNow,
                    'renewal_selling' => $renewSellNow,
                    'renewal_target' => $targetRenewSell,
                    'margin' => $sellNow - $newBase,
                ];

                $needSell = false;

                if (! $needRenewSell) {
                    continue;
                }
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
                'promos' => count($promos),
                'unknown' => array_values(array_unique($unknown)),
                'rows' => $changed,
                'created_rows' => $created,
                'repriced_rows' => $repriced,
                'kept_rows' => $kept,
                'promo_rows' => $promos,
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
            ['TLD dengan data promo registrasi (RDash)', count($promos)],
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
            $this->warn('Harga REGISTRASI dikunci manual (--keep) — tidak dihitung ulang otomatis (perpanjangan tetap ikut aturan margin):');
            $this->table(
                ['Ekstensi', 'Modal', 'Harga jual', 'Renew + pajak', 'Renew menurut aturan', 'Margin/domain'],
                array_map(fn (array $r) => [
                    $r['extension'],
                    $this->money($r['base_cost']),
                    $this->money($r['selling_price']),
                    $this->money($r['renewal_selling']),
                    $this->money($r['renewal_target'] ?? $r['renewal_selling']),
                    $this->money($r['margin']),
                ], $kept)
            );
        }

        if ($promos !== []) {
            $this->newLine();
            $this->line('Promo registrasi RDash (hanya registrasi siklus 1 tahun — perpanjangan tetap harga normal):');
            $this->table(
                ['Ekstensi', 'Modal normal', 'Modal promo', 'Jual normal', 'Jual promo', 'Margin promo', 'Berlaku sampai', 'Status'],
                array_map(fn (array $r) => [
                    $r['extension'],
                    $this->money($r['base_cost'] ?? 0),
                    $this->money($r['promo_base_cost'] ?? 0),
                    $this->money($r['selling_price'] ?? 0),
                    $this->money($r['promo_selling_price'] ?? 0),
                    ($r['promo_base_cost'] ?? null) !== null && ($r['promo_selling_price'] ?? null) !== null
                        ? $this->money($r['margin_promo'])
                        : '—',
                    $r['promo_ends_at'] ? substr((string) $r['promo_ends_at'], 0, 10) : '-',
                    $r['active'] ? 'AKTIF' : (($r['added'] ?? false) ? 'akan datang' : 'berakhir'),
                ], $promos)
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

    /**
     * Petakan promo_registration dari RDash ke kolom promo_* (null bila TLD tanpa promo).
     *
     * @return array<string, mixed>|null
     */
    private function promoFor(mixed $raw, float $taxFactor, float $flatMargin, float $markup, float $round, ?float $normalBase = null): ?array
    {
        if (! is_array($raw) || $raw === []) {
            return null;
        }

        // Promo RDash hanya untuk REGISTRASI SIKLUS 1 TAHUN. Bila harga periode 1
        // kosong (mis. promo hanya untuk 2 tahun), promo diabaikan — jangan sampai
        // harga promo dipakai untuk siklus yang berbeda lalu jadi lebih mahal.
        $registration = $raw['registration'] ?? null;
        $price = is_numeric($registration) ? (float) $registration : null;
        if (is_array($registration) && isset($registration['1']) && is_numeric($registration['1'])) {
            $price = (float) $registration['1'];
        }

        if ($price === null || $price <= 0) {
            return null;
        }

        // Modal promo juga kena PPN; harga jual promo = modal promo + margin (bulat ke atas).
        $base = round($price * $taxFactor, 2);

        // Pengaman: promo yang ternyata TIDAK lebih murah dari modal normal diabaikan.
        if ($normalBase !== null && $normalBase > 0 && $base > $normalBase - 0.01) {
            return null;
        }

        return [
            'promo_price' => $price,
            'promo_base_cost' => $base,
            'promo_selling_price' => $this->priceFor($base, $flatMargin, $markup, $round),
            'promo_starts_at' => $this->promoTime($raw['start_date'] ?? null),
            'promo_ends_at' => $this->promoTime($raw['end_date'] ?? null),
            'promo_note' => $this->promoNote($raw['description'] ?? null),
        ];
    }

    /**
     * Selaraskan kolom promo pada baris yang sudah ada. Mengembalikan baris laporan
     * bila ada perubahan (null bila tidak ada perubahan).
     *
     * @param  array<int, array<string, mixed>>  $rollbackUpdates
     * @return array<string, mixed>|null
     */
    private function syncPromo(DomainPrice $local, mixed $raw, float $taxFactor, float $flatMargin, float $markup, float $round, bool $apply, array &$rollbackUpdates): ?array
    {
        $promo = $this->promoFor($raw, $taxFactor, $flatMargin, $markup, $round, (float) $local->base_cost);

        $old = [
            'promo_price' => $local->promo_price === null ? null : (float) $local->promo_price,
            'promo_base_cost' => $local->promo_base_cost === null ? null : (float) $local->promo_base_cost,
            'promo_selling_price' => $local->promo_selling_price === null ? null : (float) $local->promo_selling_price,
            'promo_starts_at' => optional($local->promo_starts_at)->toDateTimeString(),
            'promo_ends_at' => optional($local->promo_ends_at)->toDateTimeString(),
            'promo_note' => $local->promo_note,
        ];

        $new = [
            'promo_price' => $promo['promo_price'] ?? null,
            'promo_base_cost' => $promo['promo_base_cost'] ?? null,
            'promo_selling_price' => $promo['promo_selling_price'] ?? null,
            'promo_starts_at' => optional($promo['promo_starts_at'] ?? null)->toDateTimeString(),
            'promo_ends_at' => optional($promo['promo_ends_at'] ?? null)->toDateTimeString(),
            'promo_note' => $promo['promo_note'] ?? null,
        ];

        if ($old === $new) {
            return null;
        }

        $rollbackUpdates[$local->id] ??= [
            'id' => $local->id,
            'base_cost' => (float) $local->base_cost,
            'renewal_cost' => (float) $local->renewal_cost,
        ];
        foreach ($old as $column => $value) {
            $rollbackUpdates[$local->id][$column] = $value;
        }

        if ($apply) {
            $local->fill($new);
            $local->save();
        }

        return [
            'extension' => $this->normExt((string) $local->extension),
            'base_cost' => (float) $local->base_cost,
            'selling_price' => (float) $local->selling_price,
            'promo_price' => $new['promo_price'],
            'promo_base_cost' => $new['promo_base_cost'],
            'promo_selling_price' => $new['promo_selling_price'],
            'margin_promo' => (float) ($new['promo_selling_price'] ?? 0) - (float) ($new['promo_base_cost'] ?? 0),
            'promo_ends_at' => $new['promo_ends_at'],
            'active' => $promo !== null && $local->promoIsActive(),
            'added' => $promo !== null,
        ];
    }

    /**
     * Baris laporan promo untuk TLD yang baru dibuat.
     *
     * @param  array<string, mixed>  $promo
     * @return array<string, mixed>
     */
    private function promoRow(DomainPrice $row, array $promo): array
    {
        return [
            'extension' => $this->normExt((string) $row->extension),
            'base_cost' => (float) $row->base_cost,
            'selling_price' => (float) $row->selling_price,
            'promo_price' => $promo['promo_price'],
            'promo_base_cost' => $promo['promo_base_cost'],
            'promo_selling_price' => $promo['promo_selling_price'],
            'margin_promo' => (float) $promo['promo_selling_price'] - (float) $promo['promo_base_cost'],
            'promo_ends_at' => optional($promo['promo_ends_at'])->toDateTimeString(),
            'active' => $row->promoIsActive(),
            'added' => true,
        ];
    }

    /** Tanggal promo dari RDash (UTC) dinormalkan ke UTC agar perbandingan status konsisten. */
    private function promoTime(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value, 'UTC')->setTimezone('UTC');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Syarat promo: HTML dari RDash dibersihkan jadi teks biasa. */
    private function promoNote(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $text = (string) preg_replace('/<\/(li|p|ul|ol|div)>/i', "\n", $value);
        $text = strip_tags($text);
        $text = (string) preg_replace('/[ \t]+/', ' ', $text);
        $text = (string) preg_replace('/\n{2,}/', "\n", $text);

        return trim($text) ?: null;
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

    /**
     * Ambil harga periode 1 tahun dari map {periode: harga}.
     *
     * Tidak ada fallback ke periode lain: harga periode 2/3 tahun tidak boleh
     * dipakai sebagai harga 1 tahun (bisa jauh berbeda).
     */
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
            foreach (['promo_price', 'promo_base_cost', 'promo_selling_price'] as $numeric) {
                if (array_key_exists($numeric, $row)) {
                    $sets[] = $row[$numeric] === null
                        ? sprintf('%s = NULL', $numeric)
                        : sprintf('%s = %.2f', $numeric, $row[$numeric]);
                }
            }
            foreach (['promo_starts_at', 'promo_ends_at', 'promo_note'] as $textual) {
                if (array_key_exists($textual, $row)) {
                    $sets[] = $row[$textual] === null || $row[$textual] === ''
                        ? sprintf('%s = NULL', $textual)
                        : sprintf("%s = '%s'", $textual, str_replace("'", "''", (string) $row[$textual]));
                }
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

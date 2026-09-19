<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\RdashDomain;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Rekonsiliasi domain RDASH ↔ order WSCRM (Lapis 1, baca-saja ke RDash).
 *
 * Sumber kebenaran tanggal adalah registrar (RDash); order WSCRM yang menyimpang
 * dilaporkan sebagai "drift". Penulisan ke order_items hanya dilakukan bila
 * $fixExpiry = true (dan secara default hanya bila RDash menyebut tanggal LEBIH JAUH,
 * artinya layanan klien selama ini under-report — bukan dipersingkat).
 */
class RdashDomainSyncService
{
    public function __construct(private readonly RdashService $rdash) {}

    /** Hasil ringkasan terakhir (dipakai command untuk cetak/JSON). */
    public const STATE_MATCHED = 'matched';

    public const STATE_DRIFT = 'drift';

    public const STATE_UNMATCHED = 'unmatched';

    public const STATE_MISSING = 'missing';

    /**
     * @param  array<string, mixed>  $filter  filter RDash: name, status, customer_id, expired_range
     * @return array<string, mixed>
     */
    public function sync(
        array $filter = [],
        bool $apply = false,
        bool $fixExpiry = false,
        bool $bothWays = false,
        int $tolerance = 0,
    ): array {
        $this->rdash->assertConfigured();

        $rows = $this->rdash->domains($filter);
        $index = $this->orderItemIndex();

        $summary = [
            'dry_run' => ! $apply,
            'fetched' => count($rows),
            'created' => 0,
            'updated' => 0,
            'matched' => 0,
            'drift' => 0,
            'unmatched' => 0,
            'missing' => 0,
            'fixed' => 0,
            'fix_skipped' => 0,
            'drift_rows' => [],
            'missing_rows' => [],
            'rollback_file' => null,
        ];

        $rollback = [];
        $seen = [];

        foreach ($rows as $row) {
            $name = RdashDomain::normalize((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $seen[$name] = true;

            $orderExpiry = null;
            $match = $this->bestMatch($index[$name] ?? collect());
            $rdashExpiry = $this->toDate($row['expired_at'] ?? null);

            $state = self::STATE_UNMATCHED;
            $driftDays = null;

            if ($match !== null) {
                $orderExpiry = $match->expires_at ? Carbon::parse($match->expires_at) : null;

                if ($rdashExpiry !== null && $orderExpiry !== null) {
                    $driftDays = $this->diffDays($orderExpiry, $rdashExpiry);
                    $state = abs($driftDays) <= $tolerance ? self::STATE_MATCHED : self::STATE_DRIFT;
                } elseif ($rdashExpiry !== null) {
                    // Order belum punya tanggal → catat sebagai drift tanpa angka
                    $state = self::STATE_DRIFT;
                } else {
                    $state = self::STATE_MATCHED;
                }
            }

            $summary[match ($state) {
                self::STATE_MATCHED => 'matched',
                self::STATE_DRIFT => 'drift',
                default => 'unmatched',
            }]++;

            // Tulis cermin rdash_domains
            // Cari baris lama: bisa lewat rdash_id, atau lewat nama (baris "missing"
            // yang tadinya tidak punya rdash_id).
            $existing = RdashDomain::query()
                ->where('rdash_id', $row['id'] ?? 0)
                ->orWhere('name', $name)
                ->first();
            if ($apply) {
                $attrs = $this->mapAttributes($row, $match, $state, $driftDays, count($index[$name] ?? []));
                if ($existing) {
                    $existing->fill($attrs)->save();
                    $summary['updated']++;
                } else {
                    RdashDomain::query()->create($attrs);
                    $summary['created']++;
                }
            } elseif (! $existing) {
                $summary['created']++;
            } else {
                $summary['updated']++;
            }

            if ($state === self::STATE_DRIFT) {
                $fixable = $rdashExpiry !== null && $match !== null
                    && ($bothWays || ($orderExpiry !== null && $rdashExpiry->greaterThan($orderExpiry)));

                $summary['drift_rows'][] = [
                    'name' => $name,
                    'order_id' => $match?->order_id,
                    'order_expires_at' => $orderExpiry?->toDateString(),
                    'rdash_expired_at' => $rdashExpiry?->toDateString(),
                    'drift_days' => $driftDays,
                    'fixable' => $fixable,
                ];

                if ($fixExpiry) {
                    if ($fixable && $match !== null) {
                        $rollback[] = [
                            'item_id' => $match->id,
                            'expires_at' => $match->expires_at?->toDateString(),
                        ];
                        if ($apply) {
                            $match->forceFill(['expires_at' => $rdashExpiry->toDateString()])->save();
                        }
                        $summary['fixed']++;
                    } else {
                        $summary['fix_skipped']++;
                    }
                }
            }
        }

        // Domain di order WSCRM yang tidak ditemukan di RDash
        foreach ($index as $name => $items) {
            if (isset($seen[$name])) {
                continue;
            }
            $item = $this->bestMatch($items);
            $summary['missing']++;
            $summary['missing_rows'][] = [
                'name' => $name,
                'order_id' => $item?->order_id,
                'order_expires_at' => $item?->expires_at?->toDateString(),
                'item_status' => $item?->status,
            ];
            if ($apply) {
                RdashDomain::query()->updateOrCreate(
                    ['name' => $name],
                    [
                        'match_status' => self::STATE_MISSING,
                        'order_id' => $item?->order_id,
                        'order_item_id' => $item?->id,
                        'order_expires_at' => $item?->expires_at?->toDateString(),
                        'order_matches' => count($items),
                        'last_synced_at' => now(),
                    ]
                );
            }
        }

        if ($fixExpiry && $apply && $rollback !== []) {
            $summary['rollback_file'] = $this->writeRollback($rollback);
        }

        $summary['rollback_preview'] = $rollback;

        Log::info('RDASH sync domains', [
            'apply' => $apply,
            'fetched' => $summary['fetched'],
            'drift' => $summary['drift'],
            'unmatched' => $summary['unmatched'],
            'missing' => $summary['missing'],
            'fixed' => $summary['fixed'],
        ]);

        return $summary;
    }

    /**
     * Semua item order bertipe domain, diindeks per nama domain ternormalisasi.
     * Nama domain diambil dari order_items.domain_name, fallback ke orders.domain_name
     * (data eksisting WSCRM mengisi nama domain di tabel orders).
     *
     * @return array<string, Collection<int, OrderItem>>
     */
    private function orderItemIndex(): array
    {
        $items = OrderItem::query()
            ->with('order')
            ->where('item_type', 'domain')
            ->get();

        $index = [];
        foreach ($items as $item) {
            $name = $item->domain_name ?: $item->order?->domain_name;
            if (! $name) {
                continue;
            }
            $key = RdashDomain::normalize((string) $name);
            if ($key === '') {
                continue;
            }
            $index[$key] ??= collect();
            $index[$key]->push($item);
        }

        return $index;
    }

    /** Pilih item paling relevan: status active, lalu expires_at terjauh. */
    private function bestMatch(Collection $items): ?OrderItem
    {
        if ($items->isEmpty()) {
            return null;
        }

        return $items
            ->sortByDesc(fn (OrderItem $item) => [
                $item->status === 'active' ? 1 : 0,
                $item->expires_at?->getTimestamp() ?? 0,
                $item->id,
            ])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapAttributes(array $row, ?OrderItem $match, string $state, ?int $driftDays, int $matchCount): array
    {
        return [
            'rdash_id' => (int) ($row['id'] ?? 0),
            'name' => RdashDomain::normalize((string) ($row['name'] ?? '')),
            'status' => isset($row['status']) ? (int) $row['status'] : null,
            'status_label' => $row['status_label'] ?? null,
            'status_reason' => $row['status_reason'] ?? null,
            'verification_status' => isset($row['verification_status']) ? (int) $row['verification_status'] : null,
            'verification_status_label' => $row['verification_status_label'] ?? null,
            'is_premium' => (bool) ($row['is_premium'] ?? false),
            'is_locked' => (bool) ($row['is_locked'] ?? false),
            'is_registrar_locked' => (bool) ($row['is_registrar_locked'] ?? false),
            'nameserver_1' => $row['nameserver_1'] ?? null,
            'nameserver_2' => $row['nameserver_2'] ?? null,
            'nameserver_3' => $row['nameserver_3'] ?? null,
            'nameserver_4' => $row['nameserver_4'] ?? null,
            'nameserver_5' => $row['nameserver_5'] ?? null,
            'notes' => $row['notes'] ?? null,
            'expired_at' => $this->toDate($row['expired_at'] ?? null)?->toDateString(),
            'rdash_created_at' => $this->toDateTime($row['created_at'] ?? null),
            'rdash_customer_id' => isset($row['customer_id']) ? (int) $row['customer_id'] : null,
            'order_id' => $match?->order_id,
            'order_item_id' => $match?->id,
            'order_expires_at' => $match?->expires_at?->toDateString(),
            'match_status' => $state,
            'drift_days' => $driftDays,
            'order_matches' => $matchCount,
            'last_synced_at' => now(),
            'payload' => $row,
        ];
    }

    private function toDate(mixed $value): ?Carbon
    {
        if (empty($value) || ! is_string($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function toDateTime(mixed $value): ?Carbon
    {
        if (empty($value) || ! is_string($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Selisih hari (positif = tanggal kedua lebih jauh). Carbon 3 tidak punya diffInDays absolut. */
    private function diffDays(CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) round(($to->getTimestamp() - $from->getTimestamp()) / 86400);
    }

    /**
     * Simpan skrip rollback sekali-jalan untuk perbaikan expires_at.
     *
     * @param  array<int, array<string, mixed>>  $rollback
     */
    private function writeRollback(array $rollback): string
    {
        $path = 'rdash/rollback-sync-domains-'.date('Ymd-His').'.sql';
        $lines = [
            '-- Rollback perbaikan expires_at order_items oleh rdash:sync-domains',
            '-- Dijalankan: '.date('c'),
            '-- Cara pakai: mysql -u <user> -p <db> < berkas ini',
            '',
        ];
        foreach ($rollback as $row) {
            $value = $row['expires_at'] === null ? 'NULL' : "'".$row['expires_at']."'";
            $lines[] = "UPDATE order_items SET expires_at = {$value} WHERE id = {$row['item_id']};";
        }
        $lines[] = '';

        Storage::disk('local')->put($path, implode("\n", $lines));

        return $path;
    }

    /** Ringkasan drift terkini dari DB (tanpa memanggil API). */
    public function summaryFromDb(): array
    {
        $counts = RdashDomain::query()
            ->select('match_status', DB::raw('COUNT(*) as total'))
            ->groupBy('match_status')
            ->pluck('total', 'match_status')
            ->all();

        return [
            'total' => array_sum($counts),
            'matched' => (int) ($counts[self::STATE_MATCHED] ?? 0),
            'drift' => (int) ($counts[self::STATE_DRIFT] ?? 0),
            'unmatched' => (int) ($counts[self::STATE_UNMATCHED] ?? 0),
            'missing' => (int) ($counts[self::STATE_MISSING] ?? 0),
            'last_synced_at' => RdashDomain::query()->max('last_synced_at'),
        ];
    }
}

<?php

namespace App\Console\Commands;

use App\Exceptions\RdashException;
use App\Services\RdashDomainSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RdashSyncDomains extends Command
{
    protected $signature = 'rdash:sync-domains
        {--apply : Tulis hasil sinkronisasi ke database (tanpa ini = dry-run)}
        {--fix-expiry : Ikut perbaiki order_items.expires_at dari RDash (butuh --apply)}
        {--both-ways : Saat --fix-expiry, perbaiki juga bila tanggal RDash lebih PENDEK (default: hanya bila lebih jauh)}
        {--tolerance=0 : Toleransi selisih hari yang masih dianggap cocok}
        {--name= : Batasi ke satu nama domain}
        {--status= : Filter status RDash (0 pending, 1 active, 2 expired, 3 pending delete, 4 pending restore, 5 in-active)}
        {--customer= : Filter customer_id RDash}
        {--json : Keluarkan hasil sebagai JSON}';

    protected $description = 'Sinkronkan domain dari API RDASH ke order WSCRM (laporan drift, baca-saja ke RDash)';

    public function handle(RdashDomainSyncService $sync): int
    {
        $apply = (bool) $this->option('apply');
        $fixExpiry = (bool) $this->option('fix-expiry');
        $bothWays = (bool) $this->option('both-ways');
        $tolerance = (int) $this->option('tolerance');

        $filter = array_filter([
            'name' => $this->option('name'),
            'status' => $this->option('status'),
            'customer_id' => $this->option('customer'),
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $summary = $sync->sync($filter, $apply, $fixExpiry, $bothWays, $tolerance);
        } catch (RdashException $e) {
            if ($this->option('json')) {
                $this->line(json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_SLASHES));
            } else {
                $this->error($e->getMessage());
            }

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line(json_encode($summary + ['ok' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $mode = $summary['dry_run'] ? 'DRY-RUN (tidak ada perubahan ditulis)' : 'APPLY (perubahan ditulis)';
        $this->info("Sinkronisasi domain RDASH — {$mode}");
        if ($summary['dry_run']) {
            $this->line('Jalankan ulang dengan --apply untuk menulis. Tambah --fix-expiry untuk memperbaiki tanggal order.');
        }
        $this->newLine();

        $this->table(['Hasil', 'Jumlah'], [
            ['Domain di RDash', $summary['fetched']],
            ['— cocok dengan order', $summary['matched']],
            ['— drift (beda tanggal)', $summary['drift']],
            ['— tanpa order di WSCRM', $summary['unmatched']],
            ['Order WSCRM tak ada di RDash', $summary['missing']],
            [$summary['dry_run'] ? 'Akan dibuat' : 'Dibuat', $summary['created']],
            [$summary['dry_run'] ? 'Akan diupdate' : 'Diupdate', $summary['updated']],
        ]);

        if ($summary['drift'] > 0) {
            $this->newLine();
            $this->warn('Drift tanggal (order vs registrar):');
            $rows = array_map(fn (array $r) => [
                $r['name'],
                $r['order_id'] ?? '-',
                $r['order_expires_at'] ?? '(kosong)',
                $r['rdash_expired_at'] ?? '-',
                $r['drift_days'] === null ? '?' : ($r['drift_days'] > 0 ? '+'.$r['drift_days'] : $r['drift_days']),
                $r['fixable'] ? 'ya' : 'tidak',
            ], array_slice($summary['drift_rows'], 0, 50));
            $this->table(['Domain', 'Order', 'Order expires', 'RDash expired', 'Selisih (hari)', 'Bisa difix'], $rows);
            if (count($summary['drift_rows']) > 50) {
                $this->line('… dan '.(count($summary['drift_rows']) - 50).' baris lain (lihat dengan --json).');
            }
        }

        if ($summary['missing'] > 0) {
            $this->newLine();
            $this->warn('Ada di order WSCRM tapi tidak ditemukan di akun RDash:');
            $rows = array_map(fn (array $r) => [
                $r['name'],
                $r['order_id'] ?? '-',
                $r['order_expires_at'] ?? '(kosong)',
                $r['item_status'] ?? '-',
            ], array_slice($summary['missing_rows'], 0, 50));
            $this->table(['Domain', 'Order', 'Order expires', 'Status item'], $rows);
        }

        if ($fixExpiry) {
            $this->newLine();
            $this->info("Perbaikan expires_at: {$summary['fixed']} ".($summary['dry_run'] ? 'akan diperbaiki' : 'diperbaiki')
                .", {$summary['fix_skipped']} dilewati (tanggal RDash lebih pendek / tak ada tanggal)");
            if (! $summary['dry_run'] && $summary['rollback_file']) {
                $this->line('Rollback SQL: storage/app/'.$summary['rollback_file']);
                $this->line('  mysql -u <user> -p <db> < '.(Storage::disk('local')->path($summary['rollback_file'])));
            }
        }

        return self::SUCCESS;
    }
}

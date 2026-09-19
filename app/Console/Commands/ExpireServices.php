<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Rapikan status yang "menggantung":
 *  1. Invoice yang lewat jatuh tempo tapi masih 'pending'/'sent' → 'overdue'.
 *  2. Order yang masa aktifnya sudah habis tapi masih 'active' → 'expired'
 *     (item-itemnya ikut di-'expired').
 *
 * Dijalankan harian dari cron (/etc/cron.d/wscrm-jobs). Idempoten: baris yang
 * sudah benar tidak disentuh lagi, jadi aman dijalankan berkali-kali.
 */
class ExpireServices extends Command
{
    protected $signature = 'services:expire
        {--grace=0 : Toleransi hari setelah jatuh tempo sebelum dianggap kedaluwarsa}
        {--dry-run : Tampilkan saja, jangan ubah apa pun}';

    protected $description = 'Tandai invoice lewat jatuh tempo → overdue dan layanan lewat masa aktif → expired';

    public function handle(): int
    {
        $grace = max(0, (int) $this->option('grace'));
        $dryRun = (bool) $this->option('dry-run');
        $now = Carbon::now();

        // ---------- 1. Invoice lewat jatuh tempo → overdue ----------
        $overdueIds = Invoice::query()
            ->whereIn('status', ['pending', 'sent'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now->copy()->startOfDay())
            ->pluck('id');

        if (! $dryRun && $overdueIds->isNotEmpty()) {
            Invoice::whereIn('id', $overdueIds)->update([
                'status' => 'overdue',
                'updated_at' => $now,
            ]);
        }

        $this->line(($dryRun ? '[dry-run] ' : '')."Invoice pending/sent lewat jatuh tempo → overdue: {$overdueIds->count()}");

        // ---------- 2. Layanan lewat masa aktif → expired ----------
        // Pakai $now (bukan startOfDay) supaya layanan yang habis hari ini pukul 23:59
        // belum dianggap mati pagi ini.
        $cutoff = $now->copy()->subDays($grace);

        $orders = Order::with('customer:id,name,email')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $cutoff)
            ->orderBy('expires_at')
            ->get();

        $rows = [];

        foreach ($orders as $order) {
            $rows[] = [
                $order->id,
                $order->domain_name ?: '-',
                $order->customer?->name ?? '-',
                $order->expires_at?->toDateString(),
                $order->billing_cycle,
            ];

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($order, $now) {
                $order->forceFill(['status' => 'expired', 'updated_at' => $now])->saveQuietly();

                $order->orderItems()
                    ->whereIn('status', ['active', 'pending'])
                    ->update(['status' => 'expired', 'updated_at' => $now]);
            });
        }

        if ($rows) {
            $this->table(['ID', 'Domain', 'Klien', 'Berakhir', 'Siklus'], $rows);
        }

        $this->line(($dryRun ? '[dry-run] ' : '')."Layanan active → expired: {$orders->count()}".($grace > 0 ? " (toleransi {$grace} hari)" : ''));

        if (! $dryRun && ($overdueIds->count() > 0 || $orders->count() > 0)) {
            Log::info('[services:expire] status dirapikan', [
                'invoices_overdue' => $overdueIds->count(),
                'orders_expired' => $orders->count(),
                'order_ids' => $orders->pluck('id')->all(),
                'grace_days' => $grace,
            ]);
        }

        return self::SUCCESS;
    }
}

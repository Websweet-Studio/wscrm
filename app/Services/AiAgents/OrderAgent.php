<?php

namespace App\Services\AiAgents;

use App\Models\Invoice;
use App\Models\Order;

/**
 * Sub-agent: manajemen order/layanan klien.
 */
class OrderAgent
{
    public function checkExpiringOrders(?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Mengecek order aktif yang berakhir bulan ini...', 'loading', 'Order Agent');
        }

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $orders = Order::with('customer', 'hostingPlan')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$start, $end])
            ->orderBy('expires_at')
            ->get();

        // Order yang sudah lewat jatuh tempo tapi status masih aktif (belum di-update sistem).
        $overdueOrders = Order::with('customer', 'hostingPlan')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->startOfDay())
            ->orderBy('expires_at')
            ->get();

        $typeLabels = [
            'hosting' => 'Hosting',
            'domain' => 'Domain',
            'service' => 'Layanan',
            'app' => 'Aplikasi',
            'web' => 'Website',
            'maintenance' => 'Maintenance',
        ];

        $mapOrder = fn(Order $o) => [
            'id' => $o->id,
            'customer' => $o->customer?->name ?? 'Tanpa customer',
            'service_type' => $typeLabels[$o->service_type] ?? $o->service_type,
            'domain_name' => $o->domain_name,
            'expires_at' => $o->expires_at?->format('d M Y'),
            'auto_renew' => (bool) $o->auto_renew,
            'days_left' => $o->expires_at ? max(0, (int) $o->expires_at->diffInDays(now())) : 0,
            'billing_cycle' => $o->billing_cycle,
            'renewal_value' => round((float) ($o->total_amount ?? 0), 2),
        ];

        $list = $orders->map($mapOrder)->values()->all();
        $overdueList = $overdueOrders->map($mapOrder)->values()->all();

        $monthLabel = now()->translatedFormat('F Y');
        $total = count($list) + count($overdueList);

        if ($onEvent) {
            $onEvent($total > 0 ? "Ditemukan {$total} order perlu perpanjangan (" . count($list) . " bulan ini, " . count($overdueList) . " sudah lewat)" : 'Tidak ada order yang perlu perpanjangan bulan ini', 'done', 'Order Agent');
        }

        return [
            'orders_expiring' => $list,
            'orders_overdue' => $overdueList,
            'total' => $total,
            'expiring_count' => count($list),
            'overdue_count' => count($overdueList),
            'month' => $monthLabel,
            'summary' => $total > 0
                ? "{$total} order perlu perpanjangan: " . count($list) . " berakhir bulan ini (" . $monthLabel . ") dan " . count($overdueList) . " sudah lewat jatuh tempo."
                : 'Tidak ada order yang perlu perpanjangan bulan ini (' . $monthLabel . ').',
        ];
    }

    public function renewOrder(?int $orderId, int $months, bool $markPaid, ?callable $onEvent = null): array
    {
        if (!$orderId) {
            return ['error' => 'ID order diperlukan.'];
        }

        $order = Order::find($orderId);
        if (!$order) {
            return ['error' => 'Order tidak ditemukan.'];
        }

        if ($onEvent) {
            $onEvent("Memperpanjang order " . ($order->customer?->name ?? '#' . $order->id) . " +{$months} bulan...", 'loading', 'Order Agent');
        }

        $months = max(1, $months);
        // Perpanjang dari tanggal jatuh tempo saat ini (bukan now), agar tidak "menelan"
        // sisa masa aktif bila order diperpanjang sebelum jatuh tempo.
        $base = $order->expires_at && $order->expires_at->isFuture() ? $order->expires_at : now();
        $newExpiry = $base->copy()->addMonths($months);
        $order->update(['expires_at' => $newExpiry]);

        $invoiceMessage = '';
        if ($markPaid) {
            $updated = Invoice::where('order_id', $order->id)
                ->whereNotIn('status', ['paid'])
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            $invoiceMessage = $updated > 0
                ? $updated . ' invoice ditandai lunas. '
                : 'Tidak ada invoice yang perlu ditandai lunas. ';
        }

        if ($onEvent) {
            $onEvent("Order diperpanjang hingga {$newExpiry->format('d M Y')}" . ($markPaid ? ', invoice ditandai lunas' : ''), 'done', 'Order Agent');
        }

        return [
            'success' => true,
            'order_id' => $order->id,
            'customer' => $order->customer?->name ?? 'Tanpa customer',
            'domain' => $order->domain_name,
            'months_added' => $months,
            'expires_at' => $newExpiry->format('d M Y'),
            'invoices_marked_paid' => $markPaid,
            'message' => $invoiceMessage . 'Masa aktif ' . ($order->customer?->name ?? 'order #' . $order->id)
                . ' diperpanjang ' . $months . ' bulan → berakhir ' . $newExpiry->format('d M Y') . '.',
        ];
    }
}

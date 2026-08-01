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

        $orders = Order::with('customer')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$start, $end])
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

        $list = $orders->map(fn (Order $o) => [
            'id' => $o->id,
            'customer' => $o->customer?->name ?? 'Tanpa customer',
            'service_type' => $typeLabels[$o->service_type] ?? $o->service_type,
            'domain_name' => $o->domain_name,
            'expires_at' => $o->expires_at?->format('d M Y'),
            'auto_renew' => (bool) $o->auto_renew,
            'days_left' => $o->expires_at ? max(0, (int) $o->expires_at->diffInDays(now())) : 0,
        ])->values()->all();

        $monthLabel = now()->translatedFormat('F Y');

        if ($onEvent) {
            $onEvent(count($list) > 0 ? "Ditemukan " . count($list) . " order berakhir bulan ini" : 'Tidak ada order aktif yang berakhir bulan ini', 'done', 'Order Agent');
        }

        return [
            'orders_expiring' => $list,
            'total' => count($list),
            'month' => $monthLabel,
            'summary' => count($list) > 0
                ? count($list) . ' order aktif akan berakhir bulan ini (' . $monthLabel . ').'
                : 'Tidak ada order aktif yang berakhir bulan ini (' . $monthLabel . ').',
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
        $newExpiry = now()->addMonths($months);
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

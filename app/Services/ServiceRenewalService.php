<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Satu tempat logika perpanjangan layanan (order + item + invoice + pembayaran).
 *
 * Dipakai oleh:
 *  - perintah artisan `service:renew` (operasional manual oleh admin/agen)
 *  - perintah artisan `service:due` (daftar layanan yang akan jatuh tempo)
 *
 * Sebelumnya perpanjangan dilakukan manual satu per satu (order, tiap item, dan
 * invoice harus disamakan sendiri) sehingga sering tidak sinkron — mis. invoice
 * renewal sudah lunas tapi layanan tetap `expired`.
 */
class ServiceRenewalService
{
    public function __construct(private InvoiceGeneratorService $invoiceGenerator) {}

    /**
     * Cari order layanan berdasarkan domain (case-insensitive) atau ID.
     */
    public function resolve(string $target): ?Order
    {
        $target = trim($target);

        if ($target === '') {
            return null;
        }

        if (ctype_digit($target)) {
            return Order::with(['customer', 'orderItems'])->find((int) $target);
        }

        return Order::with(['customer', 'orderItems'])
            ->whereRaw('LOWER(domain_name) = ?', [mb_strtolower($target)])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Invoice renewal terakhir untuk order ini yang belum dibatalkan.
     */
    public function openRenewalInvoice(Order $order): ?Invoice
    {
        return Invoice::query()
            ->where('order_id', $order->id)
            ->where('invoice_type', 'renewal')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Rumus nominal invoice renewal — sama dengan cron `invoice:generate-renewals`:
     * subtotal dihitung ulang dari item (bukan total_amount yang bisa stale),
     * lalu dikurangi diskon order + diskon loyalitas 5% (layanan aktif >= 12 bulan).
     *
     * @return array{subtotal: float, discount: float, net: float, loyalty_discount: float}
     */
    public function renewalAmounts(Order $order): array
    {
        $subtotal = (float) $order->orderItems()->get()->sum(fn ($item) => (float) $item->price * (int) $item->quantity);
        $orderDiscount = (float) ($order->discount_amount ?? 0);
        $netBeforeLoyalty = max(0, $subtotal - $orderDiscount);

        $loyalty = Carbon::parse($order->created_at)->diffInMonths(Carbon::now()) >= 12
            ? $netBeforeLoyalty * 0.05
            : 0.0;

        return [
            'subtotal' => $subtotal,
            'discount' => $orderDiscount + $loyalty,
            'loyalty_discount' => $loyalty,
            'net' => $subtotal - $orderDiscount - $loyalty,
        ];
    }

    /**
     * Buat invoice renewal TANPA mengirim email ke customer
     * (dipakai untuk pencatatan pembayaran yang sudah diterima lebih dulu).
     */
    public function createRenewalInvoice(Order $order, ?float $amount = null, ?string $extraNote = null): Invoice
    {
        $amounts = $this->renewalAmounts($order);
        $subtotal = $amount ?? $amounts['subtotal'];
        $discount = $amount !== null ? 0.0 : $amounts['discount'];

        $dueDate = $order->expires_at
            ? Carbon::parse($order->expires_at)->subDays(7)
            : Carbon::now()->addDays(7);

        if ($dueDate->lt(Carbon::now())) {
            $dueDate = Carbon::now()->addDays(3);
        }

        $notes = "Renewal invoice for {$order->domain_name} - {$order->service_type} service expiring on "
            .($order->expires_at ? Carbon::parse($order->expires_at)->format('d M Y') : '-')
            .' [manual service:renew, tanpa email]';

        if ($extraNote) {
            $notes .= ' — '.$extraNote;
        }

        return Invoice::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'invoice_number' => $this->invoiceGenerator->generateInvoiceNumber(),
            'invoice_type' => 'renewal',
            'amount' => $subtotal,
            'discount' => $discount,
            'issue_date' => Carbon::now()->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'status' => 'pending',
            'billing_cycle' => $order->billing_cycle,
            'notes' => $notes,
        ]);
    }

    /**
     * Perpanjang layanan + (opsional) catat pembayaran.
     *
     * @param  array{years?:int, mark_paid?:bool, paid_at?:string, create_invoice?:bool, amount?:float, activate_items?:bool, dry_run?:bool}  $options
     * @return array<string, mixed>
     */
    public function renew(Order $order, array $options = []): array
    {
        $years = max(1, (int) ($options['years'] ?? 1));
        $extend = (bool) ($options['extend'] ?? true);
        $markPaid = (bool) ($options['mark_paid'] ?? false);
        $createInvoice = (bool) ($options['create_invoice'] ?? false);
        $activateItems = (bool) ($options['activate_items'] ?? false);
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $amountOption = $options['amount'] ?? null;
        $amountOverride = ($amountOption !== null && $amountOption !== '') ? (float) $amountOption : null;
        $paidAt = Carbon::parse($options['paid_at'] ?? Carbon::now()->toDateTimeString());

        $oldExpiry = $order->expires_at ? Carbon::parse($order->expires_at)->toDateString() : null;
        $from = $order->expires_at ? Carbon::parse($order->expires_at) : Carbon::now();
        // --no-extend: tanggal tetap; hanya invoice/pembayaran/kerapian item yang diurus.
        $to = $extend ? $from->copy()->addYears($years) : $from->copy();
        $target = $extend ? $to->toDateString() : $oldExpiry;

        $items = $order->orderItems()->get();
        $invoice = $this->openRenewalInvoice($order);
        $amounts = $this->renewalAmounts($order);

        // Kalau invoice sudah ada, nominalnya dipakai apa adanya (invoice = sumber kebenaran);
        // nominal hasil hitung hanya dipakai ketika invoice baru dibuat.
        if ($invoice) {
            $amounts = [
                'subtotal' => (float) $invoice->amount,
                'discount' => (float) $invoice->discount,
                'net' => (float) $invoice->amount - (float) $invoice->discount,
            ];
        }

        $result = [
            'order_id' => $order->id,
            'domain' => $order->domain_name,
            'customer' => $order->customer?->name,
            'customer_email' => $order->customer?->email,
            'status' => $order->status,
            'old_expiry' => $oldExpiry,
            'new_expiry' => $target,
            'years' => $years,
            'extended' => $extend,
            'items_total' => $items->count(),
            'items_updated' => 0,
            'items_stale_status' => $items->where('status', 'pending')->count(),
            'invoice_id' => $invoice?->id,
            'invoice_number' => $invoice?->invoice_number,
            'invoice_status' => $invoice?->status,
            'invoice_created' => false,
            'amount' => $amounts['subtotal'],
            'discount' => $amounts['discount'],
            'net' => $amounts['net'],
            'paid_at' => null,
            'warnings' => [],
            'dry_run' => $dryRun,
        ];

        if ($markPaid && ! $invoice && ! $createInvoice) {
            $result['warnings'][] = 'Invoice renewal belum ada — tambahkan --invoice untuk membuatnya.';
            $markPaid = false;
        }

        if ($dryRun) {
            $result['items_updated'] = $target ? $items->count() : 0;
            $result['invoice_created'] = $markPaid && ! $invoice;
            $result['invoice_status'] = $invoice?->status ?? ($markPaid ? 'paid' : null);
            $result['paid_at'] = $markPaid ? $paidAt->toDateTimeString() : null;

            return $result;
        }

        DB::transaction(function () use ($order, $target, $extend, $activateItems, $createInvoice, $amountOverride, $markPaid, $paidAt, &$invoice, &$result) {
            if ($extend) {
                $order->update(['expires_at' => $target, 'updated_at' => Carbon::now()]);
            }

            // Selaraskan item ke tanggal jatuh tempo order (termasuk saat --no-extend).
            if ($target) {
                $itemUpdate = ['expires_at' => $target, 'updated_at' => Carbon::now()];
                if ($activateItems && $order->status === 'active') {
                    $itemUpdate['status'] = 'active';
                }
                $result['items_updated'] = $order->orderItems()->update($itemUpdate);
            } else {
                $result['warnings'][] = 'Order belum punya tanggal jatuh tempo — item tidak diselaraskan.';
            }

            if (! $invoice && $createInvoice) {
                $invoice = $this->createRenewalInvoice($order, $amountOverride);
                $result['invoice_created'] = true;
                $result['invoice_id'] = $invoice->id;
                $result['invoice_number'] = $invoice->invoice_number;
                $result['amount'] = (float) $invoice->amount;
                $result['discount'] = (float) $invoice->discount;
            }

            if ($invoice && $markPaid && $invoice->status !== 'paid') {
                $invoice->update(['status' => 'paid', 'paid_at' => $paidAt->toDateTimeString()]);
            }

            if ($invoice) {
                $invoice->refresh();
                $result['invoice_status'] = $invoice->status;
                $result['paid_at'] = $invoice->paid_at?->toDateTimeString();
                $result['net'] = (float) $invoice->amount - (float) $invoice->discount;
            }
        });

        Log::info('[service:renew] perpanjangan layanan', [
            'order_id' => $order->id,
            'domain' => $order->domain_name,
            'from' => $result['old_expiry'],
            'to' => $result['new_expiry'],
            'years' => $years,
            'items_updated' => $result['items_updated'],
            'invoice' => $result['invoice_number'],
            'invoice_status' => $result['invoice_status'],
            'paid_at' => $result['paid_at'],
            'actor' => 'artisan:service:renew',
        ]);

        return $result;
    }

    /**
     * Daftar layanan aktif yang jatuh tempo dalam N hari (termasuk yang sudah lewat).
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function due(int $days = 60, bool $includeExpired = true): \Illuminate\Support\Collection
    {
        $limit = Carbon::now()->addDays($days)->endOfDay();

        return Order::with('customer')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $limit)
            ->when(! $includeExpired, fn ($q) => $q->where('expires_at', '>=', Carbon::now()->startOfDay()))
            ->orderBy('expires_at')
            ->get()
            ->map(function (Order $order) {
                // Carbon 3: diffInDays() mengembalikan float tanpa parameter absolut,
                // jadi tanda (+/-) dihitung manual agar aman lintas versi.
                $expiry = Carbon::parse($order->expires_at)->startOfDay();
                $today = Carbon::now()->startOfDay();
                $daysLeft = $expiry->greaterThanOrEqualTo($today)
                    ? (int) $today->diffInDays($expiry)
                    : -(int) $expiry->diffInDays($today);

                return [
                    'id' => $order->id,
                    'domain' => $order->domain_name,
                    'customer' => $order->customer?->name,
                    'email' => $order->customer?->email,
                    'expires_at' => $expiry->toDateString(),
                    'days_left' => $daysLeft,
                    'amount' => (float) $order->total_amount,
                    'auto_renew' => (bool) $order->auto_renew,
                    'invoice_status' => $this->openRenewalInvoice($order)?->status,
                ];
            });
    }
}

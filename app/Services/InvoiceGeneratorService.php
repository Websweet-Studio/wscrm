<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceGeneratorService
{
    /**
     * Terbitkan invoice perpanjangan untuk layanan aktif yang akan jatuh tempo.
     *
     * Dua penjaga anti-dobel (dulu bocor → invoice ganda untuk periode yang sama):
     *  1. Tidak ada invoice renewal yang belum lunas untuk order ini (status
     *     pending/sent/overdue) — apa pun tanggalnya.
     *  2. Belum ada invoice renewal untuk periode jatuh tempo yang sama
     *     (`invoices.period_end` = tanggal jatuh tempo yang ditagih).
     */
    public function generateRenewalInvoices(int $daysBefore = 30): int
    {
        // PENTING: jangan pernah memutasi objek Carbon ini setelah dipakai binding
        // (Carbon bersifat mutable — dulu `$expiryDate->addDays(30)` di dalam closure
        // mengubah objek yang sama sehingga jendela 30 hari menjadi 60 hari).
        $windowStart = Carbon::now();
        $windowEnd = $windowStart->copy()->addDays($daysBefore);

        $expiringOrders = Order::where('status', 'active')
            ->where('auto_renew', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $windowEnd->copy())
            ->whereDoesntHave('invoices', function ($query) {
                $query->where('invoice_type', 'renewal')
                    ->whereIn('status', ['pending', 'sent', 'overdue']);
            })
            ->with(['customer', 'orderItems', 'invoices'])
            ->get();

        $generatedCount = 0;

        foreach ($expiringOrders as $order) {
            $periodEnd = $order->expires_at ? Carbon::parse($order->expires_at)->toDateString() : null;

            // Sudah pernah ditagih untuk periode ini? (walau sudah lunas / dibatalkan)
            if ($periodEnd && $order->invoices
                ->where('invoice_type', 'renewal')
                ->where('status', '!=', 'cancelled')
                ->contains(fn ($inv) => $inv->period_end
                    && Carbon::parse($inv->period_end)->toDateString() === $periodEnd)) {
                continue;
            }

            $invoice = DB::transaction(function () use ($order, $periodEnd) {
                // Kunci baris order supaya cron & tombol admin tidak bisa balapan.
                $fresh = Order::query()->lockForUpdate()->with(['customer'])->find($order->id);

                if (! $fresh || $fresh->status !== 'active' || ! $fresh->expires_at) {
                    return null;
                }

                $existing = Invoice::query()
                    ->where('order_id', $fresh->id)
                    ->where('invoice_type', 'renewal')
                    ->whereIn('status', ['pending', 'sent', 'overdue'])
                    ->lockForUpdate()
                    ->exists();

                if ($existing) {
                    return null;
                }

                if ($periodEnd) {
                    $alreadyBilled = Invoice::query()
                        ->where('order_id', $fresh->id)
                        ->where('invoice_type', 'renewal')
                        ->where('status', '!=', 'cancelled')
                        ->whereDate('period_end', $periodEnd)
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyBilled) {
                        return null;
                    }
                }

                return $this->createRenewalInvoiceFor($fresh, $periodEnd);
            });

            if (! $invoice) {
                continue;
            }

            // Kirim email invoice renewal ke customer (antri via InvoiceEmail/ShouldQueue).
            try {
                if ($order->customer?->email) {
                    $invoice->setRelation('customer', $order->customer);
                    $invoice->setRelation('order', $order);
                    Mail::to($order->customer->email)
                        ->queue(new \App\Mail\InvoiceEmail($invoice));
                } else {
                    Log::warning("Invoice #{$invoice->invoice_number}: customer tanpa email, email dilewati.");
                }
            } catch (\Throwable $e) {
                // Kegagalan email tidak boleh menghentikan penerbitan invoice berikutnya.
                Log::error("Gagal kirim email invoice #{$invoice->invoice_number}: ".$e->getMessage());
            }

            Log::info("Generated renewal invoice #{$invoice->invoice_number} for order #{$order->id} - {$order->domain_name}");

            $generatedCount++;
        }

        return $generatedCount;
    }

    /**
     * Buat baris invoice renewal untuk satu order (dipanggil di dalam transaksi).
     */
    private function createRenewalInvoiceFor(Order $order, ?string $periodEnd): Invoice
    {
        // Hitung ulang dari item (bukan total_amount) agar tahan data stale,
        // lalu kurangi diskon order. Konsisten dengan createAndSendInvoice.
        $subtotal = (float) $order->orderItems->sum(fn ($item) => $item->price * $item->quantity);
        $orderDiscount = (float) ($order->discount_amount ?? 0);
        $netAmount = max(0, $subtotal - $orderDiscount);

        // Diskon loyalitas (>= 12 bulan) — perhitungannya di Order supaya tidak
        // bergantung pada quirks Carbon 3 dan bisa dinyalakan/dimatikan satu tempat.
        $loyaltyDiscount = $order->loyaltyDiscount($netAmount);

        // Jatuh tempo = 7 hari sebelum masa aktif berakhir.
        $dueDate = Carbon::parse($order->expires_at)->copy()->subDays(7);
        if ($dueDate->lt(Carbon::now())) {
            $dueDate = Carbon::now()->copy()->addDays(3); // Sudah lewat → beri 3 hari
        }

        return Invoice::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_type' => 'renewal',
            // amount = subtotal BRUTO, discount = total potongan → net = amount - discount.
            'amount' => $subtotal,
            'discount' => $orderDiscount + $loyaltyDiscount,
            'issue_date' => Carbon::now()->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'period_end' => $periodEnd,
            'status' => 'pending',
            'billing_cycle' => $order->billing_cycle,
            'notes' => "Renewal invoice for {$order->domain_name} - {$order->service_type} service expiring on ".Carbon::parse($order->expires_at)->format('d M Y'),
        ]);
    }

    /**
     * Nomor invoice urut per bulan: INV-YYYY-MM-NNNN.
     *
     * Ambil nomor TERBESAR (bukan baris terbaru) supaya nomor yang pernah dipakai
     * tidak diulang setelah invoice dihapus, dan dukung nomor > 9999.
     */
    public function generateInvoiceNumber(): string
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        $prefix = "INV-{$year}-{$month}-";

        $maxNumber = Invoice::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->get()
            ->map(function ($invoice) use ($prefix) {
                $suffix = substr($invoice->invoice_number, strlen($prefix));

                return ctype_digit((string) $suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        $candidate = sprintf('%s%04d', $prefix, $maxNumber + 1);

        // Jaring pengaman terakhir: nomor invoice punya unique index di DB.
        $attempt = 0;
        while (Invoice::where('invoice_number', $candidate)->exists() && $attempt < 50) {
            $maxNumber++;
            $candidate = sprintf('%s%04d', $prefix, $maxNumber + 1);
            $attempt++;
        }

        return $candidate;
    }
}

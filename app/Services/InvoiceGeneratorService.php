<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;

class InvoiceGeneratorService
{
    public function generateRenewalInvoices(int $daysBefore = 30): int
    {
        $expiryDate = Carbon::now()->addDays($daysBefore);

        // Find active services (orders) that will expire within the specified days
        $expiringOrders = Order::where('status', 'active')
            ->where('auto_renew', true) // Only generate for auto-renewing services
            ->where('expires_at', '<=', $expiryDate)
            ->whereDoesntHave('invoices', function ($query) use ($expiryDate) {
                // Don't generate if there's already a renewal invoice for this period
                $query->where('invoice_type', 'renewal')
                    ->where('due_date', '>=', Carbon::now())
                    ->where('due_date', '<=', $expiryDate->addDays(30));
            })
            ->with(['customer'])
            ->get();

        $generatedCount = 0;

        foreach ($expiringOrders as $order) {
            // Hitung ulang dari item (bukan total_amount) agar tahan data stale,
            // lalu kurangi diskon order. Konsisten dengan createAndSendInvoice.
            $subtotal = (float) $order->orderItems->sum(fn ($item) => $item->price * $item->quantity);
            $orderDiscount = (float) ($order->discount_amount ?? 0);
            $netAmount = max(0, $subtotal - $orderDiscount);

            // Apply renewal discount if service has been active for more than 1 year
            $serviceAge = Carbon::parse($order->created_at)->diffInMonths(Carbon::now());
            $loyaltyDiscount = 0;
            if ($serviceAge >= 12) {
                $loyaltyDiscount = $netAmount * 0.05; // 5% discount for loyal customers
            }

            // Generate due date (7 days before expiry)
            $dueDate = Carbon::parse($order->expires_at)->subDays(7);
            if ($dueDate->lt(Carbon::now())) {
                $dueDate = Carbon::now()->addDays(3); // If already past, give 3 days
            }

            // Create renewal invoice (amount = subtotal, discount = semua diskon
            // digabung, agar netTotal() dan template email konsisten)
            $invoice = Invoice::create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_type' => 'renewal',
                'amount' => $subtotal,
                'discount' => $orderDiscount + $loyaltyDiscount,
                'issue_date' => Carbon::now()->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'status' => 'pending',
                'billing_cycle' => $order->billing_cycle,
                'notes' => "Renewal invoice for {$order->domain_name} - {$order->service_type} service expiring on ".Carbon::parse($order->expires_at)->format('d M Y'),
            ]);

            // Kirim email invoice renewal ke customer (antri via InvoiceEmail/ShouldQueue).
            $invoice->setRelation('customer', $order->customer);
            $invoice->setRelation('order', $order);
            \Illuminate\Support\Facades\Mail::to($order->customer->email)
                ->queue(new \App\Mail\InvoiceEmail($invoice));

            \Log::info("Generated renewal invoice #{$invoice->invoice_number} for order #{$order->id} - {$order->domain_name}");

            // TODO: Send notification to admin about generated renewal invoices
            $generatedCount++;
        }

        return $generatedCount;
    }

    public function generateInvoiceNumber(): string
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $lastInvoice = Invoice::query()
            ->where('invoice_number', 'like', "INV-{$year}-{$month}-%")
            ->latest('id')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('INV-%d-%s-%04d', $year, $month, $newNumber);
    }
}

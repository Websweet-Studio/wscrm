<?php

namespace App\Observers;

use App\Models\AiCredit;
use App\Models\AiPackage;
use App\Models\AiTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Kredit AI otomatis bertambah saat invoice topup lunas, dan dikembalikan
 * (refund) saat invoice topup yang sudah lunas dibatalkan.
 *
 * Idempoten: transaksi purchase/refund hanya dibuat sekali per invoice.
 * Seluruh perubahan saldo dibungkus transaksi database.
 */
class InvoiceObserver
{
    public function saved(Invoice $invoice): void
    {
        if (! $invoice->ai_package_id) {
            return;
        }

        $wasPaid = $invoice->getOriginal('status') === 'paid';
        $isPaid = $invoice->status === 'paid';

        if ($isPaid && ! $wasPaid) {
            DB::transaction(fn() => $this->credit($invoice));

            return;
        }

        if ($wasPaid && ! $isPaid) {
            DB::transaction(fn() => $this->refund($invoice));
        }
    }

    private function credit(Invoice $invoice): void
    {
        $alreadyCredited = AiTransaction::where('invoice_id', $invoice->id)
            ->where('source', 'purchase')
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $package = AiPackage::find($invoice->ai_package_id);

        if (! $package) {
            Log::warning("InvoiceObserver: ai_package #{$invoice->ai_package_id} tidak ditemukan utk invoice #{$invoice->id}");

            return;
        }

        AiTransaction::create([
            'customer_id' => $invoice->customer_id,
            'type' => 'in',
            'source' => 'purchase',
            'credits' => $package->credits,
            'ai_package_id' => $package->id,
            'invoice_id' => $invoice->id,
            'description' => "Topup paket {$package->name}",
        ]);

        AiCredit::firstOrCreate(['customer_id' => $invoice->customer_id])
            ->increment('balance', $package->credits);
    }

    private function refund(Invoice $invoice): void
    {
        $purchase = AiTransaction::where('invoice_id', $invoice->id)
            ->where('source', 'purchase')
            ->first();

        if (! $purchase) {
            return;
        }

        $alreadyRefunded = AiTransaction::where('invoice_id', $invoice->id)
            ->where('source', 'refund')
            ->exists();

        if ($alreadyRefunded) {
            return;
        }

        $credits = (int) $purchase->credits;

        AiTransaction::create([
            'customer_id' => $invoice->customer_id,
            'type' => 'out',
            'source' => 'refund',
            'credits' => -$credits,
            'ai_package_id' => $purchase->ai_package_id,
            'invoice_id' => $invoice->id,
            'description' => 'Refund topup (invoice dibatalkan)',
        ]);

        // Kurangi saldo tanpa membuatnya negatif.
        $credit = AiCredit::firstOrCreate(['customer_id' => $invoice->customer_id]);
        $credit->balance = max(0, (int) $credit->balance - $credits);
        $credit->save();
    }
}

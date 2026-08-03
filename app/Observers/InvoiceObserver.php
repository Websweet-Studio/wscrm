<?php

namespace App\Observers;

use App\Models\AiCredit;
use App\Models\AiPackage;
use App\Models\AiTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

/**
 * Kredit AI otomatis bertambah saat invoice topup lunas.
 * Idempoten: transaksi purchase hanya dibuat sekali per invoice.
 */
class InvoiceObserver
{
    public function saved(Invoice $invoice): void
    {
        if ($invoice->status !== 'paid' || ! $invoice->ai_package_id) {
            return;
        }

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
}

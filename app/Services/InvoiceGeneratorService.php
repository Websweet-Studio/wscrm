<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceGeneratorService
{
    public function generateRenewalInvoices(int $daysBefore = 30): int
    {
        // TODO: Implement renewal invoice generation based on orders
        return 0;
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

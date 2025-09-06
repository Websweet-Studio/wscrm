<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Service;
use Carbon\Carbon;

class InvoiceGeneratorService
{
    public function generateRenewalInvoices(int $daysBefore = 30): int
    {
        $services = Service::query()
            ->with(['customer', 'hostingPlan'])
            ->where('status', 'active')
            ->where('expires_at', '<=', Carbon::now()->addDays($daysBefore))
            ->where('expires_at', '>', Carbon::now())
            ->get();

        $invoicesGenerated = 0;

        foreach ($services as $service) {
            if ($this->shouldGenerateInvoice($service)) {
                $this->createRenewalInvoice($service);
                $invoicesGenerated++;
            }
        }

        return $invoicesGenerated;
    }

    public function createRenewalInvoice(Service $service): Invoice
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        $amount = $this->calculateRenewalAmount($service);
        
        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_type' => 'renewal',
            'service_id' => $service->id,
            'customer_id' => $service->customer_id,
            'amount' => $amount,
            'issue_date' => Carbon::now(),
            'due_date' => $service->expires_at->subDays(7), // 7 days before expiry
            'billing_cycle' => $this->determineBillingCycle($service),
            'status' => 'pending',
            'notes' => "Renewal for {$service->domain_name} ({$service->service_type})",
        ]);
    }

    public function createSetupInvoice(Service $service, float $amount): Invoice
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        
        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_type' => 'setup',
            'service_id' => $service->id,
            'customer_id' => $service->customer_id,
            'amount' => $amount,
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(14), // 14 days to pay
            'billing_cycle' => $this->determineBillingCycle($service),
            'status' => 'pending',
            'notes' => "Setup invoice for {$service->domain_name} ({$service->service_type})",
        ]);
    }

    protected function shouldGenerateInvoice(Service $service): bool
    {
        // Check if renewal invoice already exists for this period
        return !Invoice::query()
            ->where('service_id', $service->id)
            ->where('invoice_type', 'renewal')
            ->where('due_date', '>=', $service->expires_at->subDays(7))
            ->where('due_date', '<=', $service->expires_at)
            ->exists();
    }

    protected function calculateRenewalAmount(Service $service): float
    {
        if ($service->service_type === 'hosting' && $service->hostingPlan) {
            return $service->hostingPlan->selling_price;
        }

        // Default domain renewal price (could be from settings)
        if ($service->service_type === 'domain') {
            return 150000; // Default domain renewal price
        }

        return 0;
    }

    protected function determineBillingCycle(Service $service): string
    {
        // Determine based on service expiry pattern
        $now = Carbon::now();
        $expiresAt = $service->expires_at;
        $monthsUntilExpiry = $now->diffInMonths($expiresAt);

        if ($monthsUntilExpiry <= 1) {
            return 'monthly';
        } elseif ($monthsUntilExpiry <= 3) {
            return 'quarterly';
        } elseif ($monthsUntilExpiry <= 6) {
            return 'semi_annually';
        }

        return 'annually';
    }

    protected function generateInvoiceNumber(): string
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

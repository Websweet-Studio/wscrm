<?php

namespace App\Console\Commands;

use App\Services\InvoiceGeneratorService;
use Illuminate\Console\Command;

class GenerateRenewalInvoices extends Command
{
    protected $signature = 'invoice:generate-renewals {--days=30 : Days before expiry to generate invoices}';

    protected $description = 'Generate renewal invoices for services expiring soon';

    public function handle(InvoiceGeneratorService $invoiceGenerator): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Generating renewal invoices for services expiring within {$days} days...");
        
        $invoicesGenerated = $invoiceGenerator->generateRenewalInvoices($days);
        
        if ($invoicesGenerated > 0) {
            $this->info("Successfully generated {$invoicesGenerated} renewal invoice(s).");
        } else {
            $this->info('No renewal invoices need to be generated at this time.');
        }

        return Command::SUCCESS;
    }
}

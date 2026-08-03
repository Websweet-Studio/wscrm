<?php

use App\Models\AiCredit;
use App\Models\AiPackage;
use App\Models\AiTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->package = AiPackage::create([
        'name' => 'Starter 10K',
        'credits' => 5000,
        'price' => 50000,
        'is_active' => true,
    ]);
});

it('adds balance once when a topup invoice becomes paid', function () {
    $invoice = Invoice::create([
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-TEST-'.uniqid(),
        'invoice_type' => 'topup',
        'amount' => 50000,
        'ai_package_id' => $this->package->id,
        'status' => 'pending',
        'issue_date' => now(),
        'due_date' => now()->addDays(7),
    ]);

    $invoice->update(['status' => 'paid', 'paid_at' => now()]);

    expect(AiCredit::currentBalance($this->customer->id))->toBe(5000)
        ->and(AiTransaction::count())->toBe(1);

    // Simulasi saved terulang (update lain) — tidak boleh dobel.
    $invoice->update(['notes' => 'tidak penting']);

    expect(AiCredit::currentBalance($this->customer->id))->toBe(5000)
        ->and(AiTransaction::count())->toBe(1);
});

it('ignores invoices without ai package', function () {
    $invoice = Invoice::create([
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-TEST-'.uniqid(),
        'invoice_type' => 'setup',
        'amount' => 100000,
        'status' => 'pending',
        'issue_date' => now(),
        'due_date' => now()->addDays(7),
    ]);

    $invoice->update(['status' => 'paid', 'paid_at' => now()]);

    expect(AiCredit::currentBalance($this->customer->id))->toBe(0)
        ->and(AiTransaction::count())->toBe(0);
});

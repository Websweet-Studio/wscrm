<?php

use App\Models\AiCredit;
use App\Models\AiPackage;
use App\Models\AiTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->otherCustomer = Customer::factory()->create();

    $this->package = AiPackage::create([
        'name' => 'Starter 10K',
        'credits' => 5000,
        'price' => 50000,
        'is_active' => true,
    ]);

    $this->paymentAccount = PaymentAccount::create([
        'type' => 'bank',
        'name' => 'BCA 1234567890',
        'account_number' => '1234567890',
        'account_name' => 'PT Contoh',
        'is_active' => true,
        'sort' => 1,
    ]);

    $this->topupInvoice = Invoice::create([
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-TEST-'.uniqid(),
        'invoice_type' => 'topup',
        'amount' => $this->package->price,
        'ai_package_id' => $this->package->id,
        'status' => 'sent',
        'issue_date' => now(),
        'due_date' => now()->addDays(7),
    ]);
});

it('customer selects payment method and invoice becomes sent with method attached', function () {
    $this->actingAs($this->customer, 'customer')
        ->post(route('customer.invoices.process-payment', $this->topupInvoice), [
            'payment_account_id' => $this->paymentAccount->id,
        ])
        ->assertRedirect(route('customer.invoices.show', $this->topupInvoice));

    $this->topupInvoice->refresh();

    expect($this->topupInvoice->status)->toBe('sent')
        ->and($this->topupInvoice->payment_account_id)->toBe($this->paymentAccount->id)
        ->and($this->topupInvoice->payment_method)->toBe('bank')
        ->and($this->topupInvoice->isPaid())->toBeFalse();
});

it('customer submits payment proof and invoice becomes pending', function () {
    $this->actingAs($this->customer, 'customer')
        ->post(route('customer.invoices.confirm-payment', $this->topupInvoice), [
            'payment_proof' => 'https://bukti.example.com/proof-123.jpg',
        ])
        ->assertRedirect(route('customer.invoices.show', $this->topupInvoice));

    $this->topupInvoice->refresh();

    expect($this->topupInvoice->status)->toBe('pending')
        ->and($this->topupInvoice->payment_proof)->toBe('https://bukti.example.com/proof-123.jpg');
});

it('full flow: pay via admin then AI credit granted exactly once', function () {
    // 1. Customer pilih metode pembayaran.
    $this->actingAs($this->customer, 'customer')
        ->post(route('customer.invoices.process-payment', $this->topupInvoice), [
            'payment_account_id' => $this->paymentAccount->id,
        ]);

    // 2. Customer kirim bukti.
    $this->actingAs($this->customer, 'customer')
        ->post(route('customer.invoices.confirm-payment', $this->topupInvoice), [
            'payment_proof' => 'https://bukti.example.com/proof-123.jpg',
        ]);

    // 3. Admin verifikasi & tandai lunas.
    $this->topupInvoice->refresh()->markAsPaid();

    $this->topupInvoice->refresh();

    expect($this->topupInvoice->isPaid())->toBeTrue()
        ->and(AiCredit::currentBalance($this->customer->id))->toBe($this->package->credits)
        ->and(AiTransaction::where('invoice_id', $this->topupInvoice->id)->count())->toBe(1);

    // 4. Update lain pada invoice yang sudah lunas tidak menggandakan kredit.
    $this->topupInvoice->update(['notes' => 'verifikasi selesai']);

    expect(AiCredit::currentBalance($this->customer->id))->toBe($this->package->credits)
        ->and(AiTransaction::where('invoice_id', $this->topupInvoice->id)->count())->toBe(1);
});

it('rejects payment action on already paid invoice', function () {
    $this->topupInvoice->update(['status' => 'paid', 'paid_at' => now()]);

    $this->actingAs($this->customer, 'customer')
        ->post(route('customer.invoices.process-payment', $this->topupInvoice), [
            'payment_account_id' => $this->paymentAccount->id,
        ])
        ->assertRedirect(route('customer.invoices.show', $this->topupInvoice))
        ->assertSessionHas('error');

    $this->actingAs($this->customer, 'customer')
        ->post(route('customer.invoices.confirm-payment', $this->topupInvoice), [
            'payment_proof' => 'https://bukti.example.com/proof.jpg',
        ])
        ->assertRedirect(route('customer.invoices.show', $this->topupInvoice))
        ->assertSessionHas('error');
});

it('forbids other customer from processing payment of foreign invoice', function () {
    $this->actingAs($this->otherCustomer, 'customer')
        ->post(route('customer.invoices.process-payment', $this->topupInvoice), [
            'payment_account_id' => $this->paymentAccount->id,
        ])
        ->assertForbidden();
});

it('rejects inactive payment method', function () {
    $inactive = PaymentAccount::create([
        'type' => 'qris',
        'name' => 'QRIS Mati',
        'is_active' => false,
        'sort' => 99,
    ]);

    $this->actingAs($this->customer, 'customer')
        ->post(route('customer.invoices.process-payment', $this->topupInvoice), [
            'payment_account_id' => $inactive->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

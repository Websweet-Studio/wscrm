<?php

use App\Models\AiCredit;
use App\Models\AiPackage;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = Customer::factory()->create();
});

it('renders token dashboard with balance and endpoint', function () {
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 2500]);

    $this->actingAs($this->customer, 'customer')
        ->get('/customer/ai')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Customer/Ai/Index')
            ->where('balance', 2500)
            ->has('models')
            ->has('endpoint'));
});

it('generates a customer api key', function () {
    $this->actingAs($this->customer, 'customer')
        ->post('/customer/ai/api-key')
        ->assertOk()
        ->assertJsonStructure(['api_key']);

    $credit = AiCredit::where('customer_id', $this->customer->id)->first();
    expect($credit)->not->toBeNull()
        ->and($credit->api_key)->not->toBeNull();
});

it('creates a topup invoice when customer buys a package', function () {
    $package = AiPackage::create([
        'name' => 'Starter 10K',
        'credits' => 5000,
        'price' => 50000,
        'discount_amount' => 5000,
        'is_active' => true,
    ]);

    $this->actingAs($this->customer, 'customer')
        ->post("/customer/ai/packages/{$package->id}/buy")
        ->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'customer_id' => $this->customer->id,
        'invoice_type' => 'topup',
        'ai_package_id' => $package->id,
        'amount' => 50000,
        'discount' => 5000,
    ]);
});

it('adds credit balance after customer confirms payment', function () {
    $package = AiPackage::create([
        'name' => 'Starter 10K',
        'credits' => 5000,
        'price' => 50000,
        'is_active' => true,
    ]);

    $invoice = Invoice::create([
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-TEST-'.uniqid(),
        'invoice_type' => 'topup',
        'amount' => 50000,
        'ai_package_id' => $package->id,
        'status' => 'sent',
        'issue_date' => now(),
        'due_date' => now()->addDays(7),
    ]);

    $this->actingAs($this->customer, 'customer')
        ->post("/customer/invoices/{$invoice->id}/confirm-payment")
        ->assertRedirect();

    expect(AiCredit::currentBalance($this->customer->id))->toBe($package->credits);
});

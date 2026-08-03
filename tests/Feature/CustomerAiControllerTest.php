<?php

use App\Models\AiConversation;
use App\Models\AiCredit;
use App\Models\AiMessage;
use App\Models\AiModel;
use App\Models\AiPackage;
use App\Models\AiProvider;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = Customer::factory()->create();
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

it('deducts credits when customer chats via stream', function () {
    $provider = AiProvider::create([
        'name' => 'P',
        'endpoint' => 'https://p.example.com/v1',
        'api_key' => Crypt::encryptString('secret'),
        'is_active' => true,
    ]);
    AiModel::create([
        'provider_id' => $provider->id,
        'model_key' => 'test-model',
        'input_rate' => 1,
        'output_rate' => 1,
        'is_active' => true,
    ]);
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 100]);

    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Balasan AI']]],
            'usage' => ['prompt_tokens' => 1000, 'completion_tokens' => 0],
        ]),
    ]);

    $this->actingAs($this->customer, 'customer')
        ->post('/customer/ai/chat/stream', ['message' => 'halo'])
        ->assertOk()
        ->sendContent(); // stream diproses saat response dikirim

    expect(AiCredit::currentBalance($this->customer->id))->toBe(99)
        ->and(AiConversation::count())->toBe(1)
        ->and(AiMessage::where('role', 'agent')->count())->toBe(1);
});

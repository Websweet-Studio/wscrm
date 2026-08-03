<?php

use App\Models\AiCredit;
use App\Models\AiPackage;
use App\Models\AiProvider;
use App\Models\AiTransaction;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'super_admin']);
    $this->customer = Customer::factory()->create();
});

it('creates a provider with encrypted api key', function () {
    $this->actingAs($this->user)
        ->post('/admin/ai/providers', [
            'name' => 'OpenRouter',
            'endpoint' => 'https://openrouter.ai/api/v1',
            'api_key' => 'sk-rahasia',
            'is_active' => true,
        ])
        ->assertRedirect();

    $provider = AiProvider::first();
    expect($provider)->not->toBeNull()
        ->and($provider->name)->toBe('OpenRouter')
        ->and($provider->api_key)->not->toBe('sk-rahasia');
});

it('creates and lists ai models', function () {
    $provider = AiProvider::create([
        'name' => 'P',
        'endpoint' => 'https://p.example.com/v1',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->post('/admin/ai/models', [
            'provider_id' => $provider->id,
            'model_key' => 'gpt-4o-mini',
            'input_rate' => 0.2,
            'output_rate' => 0.6,
            'is_active' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('ai_models', [
        'provider_id' => $provider->id,
        'model_key' => 'gpt-4o-mini',
    ]);
});

it('creates an ai package', function () {
    $this->actingAs($this->user)
        ->post('/admin/ai/packages', [
            'name' => 'Starter 10K',
            'credits' => 10000,
            'price' => 50000,
            'discount_amount' => 5000,
            'is_active' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('ai_packages', [
        'name' => 'Starter 10K',
        'credits' => 10000,
        'price' => 50000,
        'discount_amount' => 5000,
    ]);
});

it('adjusts customer credit manually with audit transaction', function () {
    $this->actingAs($this->user)
        ->post('/admin/ai/credits/adjust', [
            'customer_id' => $this->customer->id,
            'action' => 'add',
            'credits' => 100,
            'description' => 'bonus promo',
        ])
        ->assertRedirect();

    expect(AiCredit::currentBalance($this->customer->id))->toBe(100);

    $tx = AiTransaction::where('customer_id', $this->customer->id)->first();
    expect($tx)->not->toBeNull()
        ->and($tx->source)->toBe('manual_adjust')
        ->and($tx->type)->toBe('in')
        ->and($tx->credits)->toBe(100);
});

it('lists transactions page', function () {
    $this->actingAs($this->user)
        ->get('/admin/ai/transactions')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Ai/Transactions/Index'));
});

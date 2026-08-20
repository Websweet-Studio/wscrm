<?php

use App\Models\AiCredit;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->apiKey = 'wsk-test-' . Str::random(40);
    AiCredit::create([
        'customer_id' => $this->customer->id,
        'balance' => 100,
        'api_key' => Crypt::encryptString($this->apiKey),
    ]);

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
});

it('rejects request without bearer token', function () {
    $this->postJson('/api/v1/chat/completions', ['messages' => [['role' => 'user', 'content' => 'halo']]])
        ->assertStatus(401);
});

it('rejects invalid api key', function () {
    $this->withHeaders(['Authorization' => 'Bearer salah'])
        ->postJson('/api/v1/chat/completions', ['messages' => [['role' => 'user', 'content' => 'halo']]])
        ->assertStatus(401);
});

it('rejects empty messages', function () {
    $this->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->postJson('/api/v1/chat/completions', ['messages' => []])
        ->assertStatus(400);
});

it('rejects unknown model with 404', function () {
    $this->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->postJson('/api/v1/chat/completions', [
            'model' => 'tidak-ada',
            'messages' => [['role' => 'user', 'content' => 'halo']],
        ])
        ->assertStatus(404);
});

it('returns openai-compatible response and deducts credits', function () {
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Halo, ada yang bisa dibantu?']]],
            'usage' => ['prompt_tokens' => 1000, 'completion_tokens' => 500],
        ]),
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->postJson('/api/v1/chat/completions', [
            'model' => 'test-model',
            'messages' => [['role' => 'user', 'content' => 'halo']],
        ])
        ->assertOk()
        ->assertJsonPath('object', 'chat.completion')
        ->assertJsonPath('model', 'test-model')
        ->assertJsonPath('choices.0.message.content', 'Halo, ada yang bisa dibantu?')
        ->assertJsonPath('usage.prompt_tokens', 1000)
        ->assertJsonPath('credits_used', 1)
        ->assertJsonPath('balance_after', 99);

    expect(AiCredit::currentBalance($this->customer->id))->toBe(99);
});

it('returns 429 when balance is insufficient', function () {
    AiCredit::where('customer_id', $this->customer->id)->update(['balance' => 0]);

    Http::fake(['*/chat/completions' => Http::response(['choices' => [], 'usage' => []])]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->postJson('/api/v1/chat/completions', [
            'messages' => [['role' => 'user', 'content' => 'halo']],
        ])
        ->assertStatus(429)
        ->assertJsonPath('error.type', 'insufficient_quota');
});

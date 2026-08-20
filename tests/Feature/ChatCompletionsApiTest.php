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

it('passes through upstream response verbatim and deducts credits from usage', function () {
    // Bersih: /api/v1 adalah passthrough ke gateway AI. Body respons dikembalikan apa
    // adanya dari upstream, bukan di-remap. Kredit dihitung dari token usage & saldo
    // dipotong via transaksi (bukan field tambahan di body).
    $upstreamBody = [
        'id' => 'chatcmpl-test123',
        'object' => 'chat.completion',
        'created' => 1234567890,
        'model' => 'deepseek/deepseek-v4-pro',
        'choices' => [[
            'index' => 0,
            'message' => ['role' => 'assistant', 'content' => 'Halo, ada yang bisa dibantu?'],
            'finish_reason' => 'stop',
        ]],
        'usage' => ['prompt_tokens' => 1000, 'completion_tokens' => 500, 'total_tokens' => 1500],
    ];

    Http::fake(['*/chat/completions' => Http::response($upstreamBody)]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->postJson('/api/v1/chat/completions', [
            'model' => 'test-model',
            'messages' => [['role' => 'user', 'content' => 'halo']],
        ])
        ->assertOk()
        ->assertJsonPath('object', 'chat.completion')
        ->assertJsonPath('id', 'chatcmpl-test123')
        ->assertJsonPath('choices.0.message.content', 'Halo, ada yang bisa dibantu?')
        ->assertJsonPath('usage.prompt_tokens', 1000)
        // Passthrough mem-forward header auth/endpoint ups ke upstream provider.
        ->assertJsonMissingPath('credits_used')
        ->assertJsonMissingPath('balance_after');

    // Kredit dipotong: 1000 in + 500 out, rate 1 per 1M → (0.001 + 0.0005) → 1 kredit.
    expect(AiCredit::currentBalance($this->customer->id))->toBe(99);
});

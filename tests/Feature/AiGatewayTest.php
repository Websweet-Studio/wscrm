<?php

use App\Models\AiCredit;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\AiTransaction;
use App\Models\Customer;
use App\Services\AiGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = Customer::factory()->create();
});

function makeAiProvider(string $name, string $endpoint, int $sort = 1): AiProvider
{
    return AiProvider::create([
        'name' => $name,
        'endpoint' => $endpoint,
        'api_key' => Crypt::encryptString('secret'),
        'is_active' => true,
        'sort_order' => $sort,
    ]);
}

function makeAiModel(AiProvider $provider, string $modelKey, float $inputRate = 1, float $outputRate = 1, int $sort = 1): AiModel
{
    return AiModel::create([
        'provider_id' => $provider->id,
        'model_key' => $modelKey,
        'display_name' => $modelKey,
        'input_rate' => $inputRate,
        'output_rate' => $outputRate,
        'is_active' => true,
        'sort_order' => $sort,
    ]);
}

it('deducts credits based on provider usage', function () {
    $provider = makeAiProvider('P', 'https://p1.example.com/v1');
    makeAiModel($provider, 'test-model');
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 100]);

    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Halo']]],
            'usage' => ['prompt_tokens' => 1000, 'completion_tokens' => 500, 'total_tokens' => 1500],
        ]),
    ]);

    $result = app(AiGateway::class)->chat($this->customer->id, null, [['role' => 'user', 'content' => 'tes']]);

    expect($result['credits_used'])->toBe(1) // (1000+500)/1M × rate 1 → 0.0015 → min 1 kredit
        ->and($result['balance_after'])->toBe(99);

    $tx = AiTransaction::where('customer_id', $this->customer->id)->first();
    expect($tx)->not->toBeNull()
        ->and($tx->type)->toBe('out')
        ->and($tx->source)->toBe('usage')
        ->and($tx->credits)->toBe(-1)
        ->and($tx->tokens_input)->toBe(1000)
        ->and($tx->tokens_output)->toBe(500);
});

it('blocks chat when balance is zero', function () {
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 0]);

    $this->expectException(RuntimeException::class);

    app(AiGateway::class)->chat($this->customer->id, null, [['role' => 'user', 'content' => 'halo']]);
});

it('falls back to the next active model when the first provider fails', function () {
    $bad = makeAiProvider('Bad', 'https://bad.example.com/v1', 1);
    $good = makeAiProvider('Good', 'https://good.example.com/v1', 2);
    makeAiModel($bad, 'bad-model', 1, 1, 1);
    makeAiModel($good, 'good-model', 1, 1, 2);
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 100]);

    Http::fake([
        'https://bad.example.com/*' => Http::response('oops', 500),
        'https://good.example.com/*' => Http::response([
            'choices' => [['message' => ['content' => 'Oke']]],
            'usage' => ['prompt_tokens' => 500, 'completion_tokens' => 0],
        ]),
    ]);

    $result = app(AiGateway::class)->chat($this->customer->id, null, [['role' => 'user', 'content' => 'halo']]);

    expect($result['model_key'])->toBe('good-model')
        ->and($result['provider_name'])->toBe('Good')
        ->and($result['credits_used'])->toBe(1); // (500+0)/1M × 1 → min 1 kredit
});

it('estimates tokens when provider omits usage', function () {
    $provider = makeAiProvider('P', 'https://p2.example.com/v1');
    makeAiModel($provider, 'test-model');
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 100]);

    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'jawaban']]],
        ]), // tanpa field usage
    ]);

    $result = app(AiGateway::class)->chat($this->customer->id, null, [['role' => 'user', 'content' => 'pertanyaan']]);

    expect($result['credits_used'])->toBeGreaterThan(0);

    $tx = AiTransaction::where('customer_id', $this->customer->id)->first();
    expect($tx->tokens_input)->not->toBeNull()
        ->and($tx->tokens_output)->not->toBeNull();
});

it('uses reasoning_content when content is empty (reasoning model)', function () {
    $provider = makeAiProvider('P', 'https://p3.example.com/v1');
    makeAiModel($provider, 'deepseek-v4-pro');
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 100]);

    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [[
                'message' => ['role' => 'assistant', 'content' => '', 'reasoning_content' => 'Jawaban dari penalaran model'],
                'finish_reason' => 'stop',
            ]],
            'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 50, 'total_tokens' => 150],
        ]),
    ]);

    $result = app(AiGateway::class)->chat($this->customer->id, null, [['role' => 'user', 'content' => 'tes']]);

    expect($result['content'])->toBe('Jawaban dari penalaran model');
});

it('falls back to reasoning_content in streaming when provider sends no real content', function () {
    $provider = makeAiProvider('P', 'https://p4.example.com/v1');
    makeAiModel($provider, 'deepseek-v4-pro');
    AiCredit::create(['customer_id' => $this->customer->id, 'balance' => 100]);

    // Simulasikan model reasoning yang HANYA mengirim reasoning_content (tanpa
    // content final). Tanpa fallback, klien agent (Trae/Hermes) menghapus "jawaban",
    // loop rethink, lalu error -1. Content harus di-pakai dari reasoning.
    $sse = "data: " . json_encode(['choices' => [['delta' => ['reasoning_content' => 'Jawaban dari penalaran model']]]]) . "\n\n"
        . "data: " . json_encode(['choices' => [['delta' => [], 'finish_reason' => 'stop']], 'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 50]]) . "\n\n"
        . "data: [DONE]\n\n";

    Http::fake([
        '*/chat/completions' => Http::response($sse, 200, ['Content-Type' => 'text/event-stream']),
    ]);

    $client = \App\Services\AiClient::forProvider($provider, 'deepseek-v4-pro');
    $result = $client->streamChat(
        [['role' => 'user', 'content' => 'tes']],
        0.3,
        2000,
        [],
        function () {},
    );

    expect($result['content'])->toBe('Jawaban dari penalaran model')
        ->and($result['reasoning_fallback'])->toBeTrue();
});

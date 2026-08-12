<?php

use App\Models\User;
use App\Services\AiAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'super_admin']);
    $this->actingAs($this->user);

    config([
        'services.ai.api_key' => 'test-key',
        'services.ai.endpoint' => 'https://ai.test.local/v1',
        'services.ai.model' => 'test-model',
    ]);
});

it('self-corrects when the AI hallucinates an unknown action', function () {
    Http::fake([
        '*/chat/completions' => Http::sequence()
            // Response pertama: aksi fiktif yang tidak dikenal sistem
            ->push([
                'choices' => [['message' => ['content' => '{"message":"hai","actions":[{"action":"hack_database","params":{}}]}']]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 10],
            ])
            // Response kedua (koreksi): aksi valid
            ->push([
                'choices' => [['message' => ['content' => '{"message":"oke","actions":[{"action":"list_customers","params":{}}]}']]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 10],
            ]),
    ]);

    $result = app(AiAgentService::class)->process('lihat customer');

    expect($result['actions'])->toHaveCount(1)
        ->and($result['actions'][0]['action'])->toBe('list_customers')
        ->and($result['actions'][0]['result']['error'] ?? null)->toBeNull()
        ->and($result['pending_actions'])->toBeEmpty();
});

it('does not retry on a plain informational answer without actions', function () {
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => '{"message":"Tidak ada aksi diperlukan"}']]],
            'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 5],
        ]),
    ]);

    $result = app(AiAgentService::class)->process('halo apa kabar');

    expect($result['actions'])->toBeEmpty()
        ->and($result['pending_actions'])->toBeEmpty()
        ->and($result['ai_response'])->toBe('Tidak ada aksi diperlukan');
});

it('parses a bare actions array without a message field', function () {
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => '[{"action":"list_customers","params":{}}]']]],
            'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 5],
        ]),
    ]);

    $result = app(AiAgentService::class)->process('lihat customer');

    expect($result['actions'])->toHaveCount(1)
        ->and($result['actions'][0]['action'])->toBe('list_customers');
});

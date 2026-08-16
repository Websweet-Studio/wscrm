<?php

use App\Models\AiCredit;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = \App\Models\User::factory()->admin()->create(['role' => 'super_admin']);
    $this->regularAdmin = \App\Models\User::factory()->admin()->create();
    $this->customer = Customer::factory()->create();
});

it('restricts database page to super admin', function () {
    $this->actingAs($this->regularAdmin)
        ->get(route('admin.database.index'))
        ->assertForbidden();

    $this->actingAs($this->superAdmin)
        ->get(route('admin.database.index'))
        ->assertOk();
});

it('restricts export to super admin', function () {
    $this->actingAs($this->regularAdmin)
        ->get(route('admin.database.export'))
        ->assertForbidden();
});

it('redacts plaintext api key on export', function () {
    AiCredit::create([
        'customer_id' => $this->customer->id,
        'balance' => 10,
        'api_key' => 'sk-rahasia-123',
        'api_key_hash' => hash('sha256', 'sk-rahasia-123'),
    ]);

    $response = (new \App\Http\Controllers\Admin\DatabaseController())->export();
    $path = $response->getFile()->getPathname();
    $json = json_decode(\Illuminate\Support\Facades\File::get($path), true);
    @unlink($path);

    expect($json)->not->toBeNull()
        ->and($json['tables']['ai_credits'][0]['api_key'])->toBeNull()
        ->and($json['tables']['ai_credits'][0]['api_key_hash'])->toBeNull()
        ->and($json['tables']['ai_credits'][0]['balance'])->toBe(10);
});

it('requires confirm checkbox when importing', function () {
    $json = [
        'driver' => 'sqlite',
        'version' => 2,
        'tables' => ['ai_credits' => []],
    ];
    $file = UploadedFile::fake()->createWithContent('backup.json', json_encode($json));

    $this->actingAs($this->superAdmin)
        ->post(route('admin.database.import'), ['file' => $file])
        ->assertSessionHasErrors('confirm_restore');
});

it('imports backup and overwrites rows after confirmation', function () {
    AiCredit::create([
        'customer_id' => $this->customer->id,
        'balance' => 100,
        'api_key' => 'sk-lama',
        'api_key_hash' => hash('sha256', 'sk-lama'),
    ]);

    $json = [
        'driver' => 'sqlite',
        'version' => 2,
        'tables' => [
            'ai_credits' => [
                ['id' => 1, 'customer_id' => $this->customer->id, 'balance' => 7, 'api_key' => null, 'api_key_hash' => null, 'created_at' => now(), 'updated_at' => now()],
            ],
        ],
    ];
    $file = UploadedFile::fake()->createWithContent('backup.json', json_encode($json));

    $response = $this->actingAs($this->superAdmin)
        ->post(route('admin.database.import'), ['file' => $file, 'confirm_restore' => '1']);

    $response->assertSessionHas('success');

    expect(AiCredit::first()->balance)->toBe(7)
        ->and(AiCredit::first()->api_key)->toBeNull();
});

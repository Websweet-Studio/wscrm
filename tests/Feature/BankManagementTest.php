<?php

use App\Models\PaymentAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
});

test('admin can view banks index page', function () {
    PaymentAccount::query()->create([
        'type' => 'bank',
        'name' => 'Bank A',
        'account_number' => '111',
        'account_name' => 'A',
        'is_active' => true,
        'sort' => 0,
    ]);
    PaymentAccount::query()->create([
        'type' => 'ewallet',
        'name' => 'E-Wallet B',
        'account_number' => '222',
        'account_name' => null,
        'is_active' => true,
        'sort' => 0,
    ]);
    PaymentAccount::query()->create([
        'type' => 'bank',
        'name' => 'Bank C',
        'account_number' => '333',
        'account_name' => 'C',
        'is_active' => false,
        'sort' => 1,
    ]);

    $response = $this->actingAs($this->admin)
        ->get('/admin/payments');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Banks/Index')
            ->has('payments.data', 3)
        );
});

test('admin can create a new bank', function () {
    $payload = [
        'type' => 'bank',
        'name' => 'Test Bank',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin)
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->post('/admin/payments', $payload);

    $response->assertRedirect('/admin/payments');

    $this->assertDatabaseHas('payment_accounts', [
        'type' => 'bank',
        'name' => 'Test Bank',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'is_active' => true,
    ]);
});

test('admin can update a payment account', function () {
    $payment = PaymentAccount::query()->create([
        'type' => 'bank',
        'name' => 'Old Name',
        'account_number' => '123',
        'account_name' => 'Old Account',
        'is_active' => true,
        'sort' => 0,
    ]);

    $updateData = [
        'type' => 'bank',
        'name' => 'Updated Name',
        'account_number' => '999',
        'account_name' => 'Updated Account',
        'is_active' => false,
    ];

    $response = $this->actingAs($this->admin)
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->put("/admin/payments/{$payment->id}", $updateData);

    $response->assertRedirect('/admin/payments');

    $this->assertDatabaseHas('payment_accounts', [
        'id' => $payment->id,
        'name' => 'Updated Name',
        'account_number' => '999',
        'is_active' => false,
    ]);
});

test('admin can delete a payment account', function () {
    $payment = PaymentAccount::query()->create([
        'type' => 'bank',
        'name' => 'To Delete',
        'account_number' => '123',
        'account_name' => 'Delete',
        'is_active' => true,
        'sort' => 0,
    ]);

    $response = $this->actingAs($this->admin)
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->delete("/admin/payments/{$payment->id}");

    $response->assertRedirect('/admin/payments');

    $this->assertDatabaseMissing('payment_accounts', [
        'id' => $payment->id,
    ]);
});

test('payment creation requires mandatory fields', function () {
    $response = $this->actingAs($this->admin)
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->post('/admin/payments', []);

    $response->assertSessionHasErrors([
        'type',
        'name',
    ]);
});

test('admin can toggle payment status', function () {
    $payment = PaymentAccount::query()->create([
        'type' => 'bank',
        'name' => 'Toggle',
        'account_number' => '123',
        'account_name' => 'Toggle',
        'is_active' => true,
        'sort' => 0,
    ]);

    $response = $this->actingAs($this->admin)
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->patch("/admin/payments/{$payment->id}/toggle-status");

    $response->assertRedirect();

    $payment->refresh();
    expect($payment->is_active)->toBeFalse();
});

test('unauthenticated users cannot access bank management', function () {
    $response = $this->get('/admin/payments');

    $response->assertRedirect('/login');
});

test('bank can have associated invoices', function () {
    $payment = PaymentAccount::query()->create([
        'type' => 'bank',
        'name' => 'Rel',
        'account_number' => '123',
        'account_name' => 'Rel',
        'is_active' => true,
        'sort' => 0,
    ]);

    expect($payment->invoices())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

<?php

use App\Models\Bank;
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
    Bank::factory()->count(3)->create();
    
    $response = $this->actingAs($this->admin)
        ->get('/admin/banks');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Banks/Index')
            ->has('banks.data', 3)
        );
});

test('admin can create a new bank', function () {
    $bankData = [
        'bank_name' => 'Test Bank',
        'bank_code' => 'TESTBANK',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'bank_type' => 'local',
        'admin_fee' => 2500.00,
        'is_active' => true,
    ];
    
    $response = $this->actingAs($this->admin)
        ->post('/admin/banks', $bankData);
    
    $response->assertRedirect('/admin/banks');
    
    $this->assertDatabaseHas('banks', [
        'bank_name' => 'Test Bank',
        'bank_code' => 'TESTBANK',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'bank_type' => 'local',
        'is_active' => true,
    ]);
});

test('admin can view a specific bank', function () {
    $bank = Bank::factory()->create();
    
    $response = $this->actingAs($this->admin)
        ->get("/admin/banks/{$bank->id}");
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Banks/Show')
            ->where('bank.id', $bank->id)
        );
});

test('admin can update a bank', function () {
    $bank = Bank::factory()->create([
        'bank_name' => 'Old Bank Name',
        'is_active' => true,
    ]);
    
    $updateData = [
        'bank_name' => 'Updated Bank Name',
        'bank_code' => $bank->bank_code,
        'account_number' => $bank->account_number,
        'account_name' => $bank->account_name,
        'bank_type' => $bank->bank_type,
        'admin_fee' => $bank->admin_fee,
        'is_active' => false,
    ];
    
    $response = $this->actingAs($this->admin)
        ->put("/admin/banks/{$bank->id}", $updateData);
    
    $response->assertRedirect('/admin/banks');
    
    $this->assertDatabaseHas('banks', [
        'id' => $bank->id,
        'bank_name' => 'Updated Bank Name',
        'is_active' => false,
    ]);
});

test('admin can delete a bank', function () {
    $bank = Bank::factory()->create();
    
    $response = $this->actingAs($this->admin)
        ->delete("/admin/banks/{$bank->id}");
    
    $response->assertRedirect('/admin/banks');
    
    $this->assertDatabaseMissing('banks', [
        'id' => $bank->id,
    ]);
});

test('bank code must be unique', function () {
    Bank::factory()->create(['bank_code' => 'DUPLICATE']);
    
    $bankData = [
        'bank_name' => 'Another Bank',
        'bank_code' => 'DUPLICATE',
        'account_number' => '9876543210',
        'account_name' => 'Another Account',
        'bank_type' => 'local',
        'admin_fee' => 2500.00,
        'is_active' => true,
    ];
    
    $response = $this->actingAs($this->admin)
        ->post('/admin/banks', $bankData);
    
    $response->assertSessionHasErrors(['bank_code']);
});

test('bank creation requires all mandatory fields', function () {
    $response = $this->actingAs($this->admin)
        ->post('/admin/banks', []);
    
    $response->assertSessionHasErrors([
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'bank_type',
    ]);
});

test('admin can toggle bank status', function () {
    $bank = Bank::factory()->create(['is_active' => true]);
    
    $response = $this->actingAs($this->admin)
        ->patch("/admin/banks/{$bank->id}/toggle-status");
    
    $response->assertRedirect();
    
    $bank->refresh();
    expect($bank->is_active)->toBeFalse();
});

test('unauthenticated users cannot access bank management', function () {
    $response = $this->get('/admin/banks');
    
    $response->assertRedirect('/login');
});

test('bank can have associated invoices', function () {
    $bank = Bank::factory()->create();
    
    // This test assumes Invoice model exists and has bank_id relationship
    // You may need to adjust based on your actual Invoice model implementation
    expect($bank->invoices())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

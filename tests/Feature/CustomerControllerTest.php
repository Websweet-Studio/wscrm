<?php

use App\Models\Customer;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can view customer index page', function () {
    $user = User::factory()->admin()->create();
    $customers = Customer::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/admin/customers');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Customers/Index')
            ->has('customers.data', 3)
        );
});

it('can view customer show page', function () {
    $user = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)->get("/admin/customers/{$customer->id}");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Customers/Show')
            ->has('customer')
        );
});

it('can create a customer', function () {
    $user = User::factory()->admin()->create();

    $customerData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+1234567890',
        'address' => '123 Main St',
        'city' => 'New York',
        'country' => 'USA',
        'postal_code' => '10001',
    ];

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post('/admin/customers', $customerData + ['_token' => 'test-token']);

    $response->assertRedirect();

    $this->assertDatabaseHas('customers', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'city' => 'New York',
        'status' => 'active',
    ]);
});

it('can update a customer', function () {
    $user = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $updateData = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '+0987654321',
        'address' => '456 Oak Ave',
        'city' => 'Los Angeles',
        'country' => 'USA',
        'postal_code' => '90210',
        'status' => 'inactive',
    ];

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->put("/admin/customers/{$customer->id}", $updateData + ['_token' => 'test-token']);

    $response->assertRedirect();

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'status' => 'inactive',
    ]);
});

it('can delete a customer without orders or services', function () {
    $user = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->delete("/admin/customers/{$customer->id}", ['_token' => 'test-token']);

    $response->assertRedirect();
    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});

it('can delete a customer with orders and related data', function () {
    $user = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $order = $customer->orders()->create([
        'user_id' => $customer->id,
        'order_type' => 'hosting',
        'total_amount' => 100.00,
        'status' => 'pending',
        'billing_cycle' => 'monthly',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->delete("/admin/customers/{$customer->id}", ['_token' => 'test-token']);

    $response->assertRedirect();
    // Customer should be deleted along with all related data
    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
});

it('validates required fields when creating customer', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post('/admin/customers', ['_token' => 'test-token']);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

it('validates email uniqueness when creating customer', function () {
    $user = User::factory()->admin()->create();
    $existingCustomer = Customer::factory()->create(['email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post('/admin/customers', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_token' => 'test-token',
        ]);

    $response->assertSessionHasErrors(['email']);
});

it('can search customers by name', function () {
    $user = User::factory()->admin()->create();
    Customer::factory()->create(['name' => 'John Doe']);
    Customer::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($user)->get('/admin/customers?search=John');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Customers/Index')
            ->has('customers.data', 1)
            ->where('customers.data.0.name', 'John Doe')
        );
});

it('can filter customers by status', function () {
    $user = User::factory()->admin()->create();
    Customer::factory()->create(['status' => 'active']);
    Customer::factory()->create(['status' => 'inactive']);

    $response = $this->actingAs($user)->get('/admin/customers?status=active');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Customers/Index')
            ->has('customers.data', 1)
            ->where('customers.data.0.status', 'active')
        );
});

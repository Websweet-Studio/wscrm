<?php

use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test user
    $this->user = User::factory()->create();

    // Create test data
    $this->customer = Customer::factory()->create();
    $this->hostingPlan = HostingPlan::factory()->active()->create();

    // Create an order with items to edit
    $this->order = Order::factory()->create([
        'customer_id' => $this->customer->id,
        'domain_name' => 'test-domain.com',
        'billing_cycle' => 'annually',
        'status' => 'pending',
        'total_amount' => 1000000,
    ]);

    // Create order items
    OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'item_type' => 'hosting',
        'item_id' => $this->hostingPlan->id,
        'price' => 500000,
    ]);

    OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'item_type' => 'domain',
        'item_id' => 1,
        'price' => 500000,
    ]);
});

it('can display orders edit page', function () {
    $this->actingAs($this->user);

    $response = $this->get('/admin/orders');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Admin/Orders/Index')
        ->has('orders.data')
    );
});

it('can update an order successfully', function () {
    $this->actingAs($this->user);

    $newCustomer = Customer::factory()->create();

    $updateData = [
        'customer_id' => $newCustomer->id,
        'domain_name' => 'updated-domain.com',
        'billing_cycle' => 'monthly',
        'status' => 'active',
        'expires_at' => '2025-12-31',
        'auto_renew' => true,
        'items' => [
            [
                'item_type' => 'hosting',
                'item_id' => $this->hostingPlan->id,
                'price' => 600000,
            ],
            [
                'item_type' => 'maintenance',
                'item_id' => 1,
                'price' => 300000,
            ],
        ],
    ];

    $response = $this->put("/admin/orders/{$this->order->id}", $updateData);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Pesanan berhasil diperbarui!');

    // Verify order was updated
    $this->order->refresh();
    expect($this->order->customer_id)->toBe($newCustomer->id);
    expect($this->order->domain_name)->toBe('updated-domain.com');
    expect($this->order->billing_cycle)->toBe('monthly');
    expect($this->order->status)->toBe('active');
    expect($this->order->auto_renew)->toBe(true);
    expect((float) $this->order->total_amount)->toBe(900000.0); // 600000 + 300000

    // Verify order items were updated
    $orderItems = $this->order->orderItems;
    expect($orderItems)->toHaveCount(2);

    $hostingItem = $orderItems->where('item_type', 'hosting')->first();
    expect((float) $hostingItem->price)->toBe(600000.0);

    $maintenanceItem = $orderItems->where('item_type', 'maintenance')->first();
    expect((float) $maintenanceItem->price)->toBe(300000.0);
});

it('validates required fields when updating order', function () {
    $this->actingAs($this->user);

    $response = $this->put("/admin/orders/{$this->order->id}", [
        // Missing required fields
        'domain_name' => '',
        'items' => [],
    ]);

    $response->assertSessionHasErrors([
        'customer_id',
        'billing_cycle',
        'status',
        'items',
    ]);
});

it('handles order item type validation correctly', function () {
    $this->actingAs($this->user);

    $response = $this->put("/admin/orders/{$this->order->id}", [
        'customer_id' => $this->customer->id,
        'domain_name' => 'test.com',
        'billing_cycle' => 'monthly',
        'status' => 'pending',
        'items' => [
            [
                'item_type' => 'invalid_type', // Invalid item type
                'item_id' => 1,
                'price' => 500000,
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['items.0.item_type']);
});

it('recalculates total amount correctly when updating', function () {
    $this->actingAs($this->user);

    $updateData = [
        'customer_id' => $this->customer->id,
        'domain_name' => 'test.com',
        'billing_cycle' => 'monthly',
        'status' => 'pending',
        'items' => [
            [
                'item_type' => 'app',
                'item_id' => 1,
                'price' => 2500000,
            ],
            [
                'item_type' => 'web',
                'item_id' => 2,
                'price' => 1500000,
            ],
        ],
    ];

    $response = $this->put("/admin/orders/{$this->order->id}", $updateData);

    $response->assertRedirect();

    $this->order->refresh();
    expect((float) $this->order->total_amount)->toBe(4000000.0); // 2500000 + 1500000
});

it('uses default prices when price is not provided', function () {
    $this->actingAs($this->user);

    $updateData = [
        'customer_id' => $this->customer->id,
        'domain_name' => 'test.com',
        'billing_cycle' => 'monthly',
        'status' => 'pending',
        'items' => [
            [
                'item_type' => 'app',
                'item_id' => 1,
                // No price provided, should use default
            ],
        ],
    ];

    $response = $this->put("/admin/orders/{$this->order->id}", $updateData);

    $response->assertRedirect();

    $this->order->refresh();
    $orderItem = $this->order->orderItems->first();
    expect((float) $orderItem->price)->toBe(2500000.0); // Default app price
});

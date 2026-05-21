<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

test('customer guest pages can be opened', function (string $url, string $component) {
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component($component));
})->with(function () {
    return [
        ['/customer/login', 'Customer/Auth/Login'],
        ['/customer/register', 'Customer/Auth/Register'],
        ['/customer/terms', 'Customer/Auth/Terms'],
    ];
});

test('customer authenticated pages can be opened', function () {
    $customer = Customer::factory()->create([
        'password' => Hash::make('password'),
        'status' => 'active',
    ]);

    $this->actingAs($customer, 'customer');

    $order = Order::factory()->forCustomer($customer)->active()->create([
        'expires_at' => Carbon::now()->addDays(30),
        'service_type' => 'hosting',
        'domain_name' => 'example.com',
    ]);
    OrderItem::factory()->for($order)->hosting()->create();

    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'order_id' => $order->id,
        'status' => 'sent',
        'invoice_number' => 'INV/'.Carbon::now()->format('Y').'/'.Carbon::now()->format('m').'/'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
        'amount' => 1500000,
        'issue_date' => Carbon::now()->subDay(),
        'due_date' => Carbon::now()->addDays(7),
    ]);

    $cases = [
        ['/customer/dashboard', 'Customer/Dashboard'],
        ['/customer/orders', 'Orders/Index'],
        ["/customer/orders/{$order->id}", 'Orders/Show'],
        ['/customer/services', 'Orders/Index'],
        ["/customer/services/{$order->id}", 'Orders/Show'],
        ['/customer/invoices', 'Customer/Invoices/Index'],
        ["/customer/invoices/{$invoice->id}", 'Customer/Invoices/Show'],
        ["/customer/invoices/{$invoice->id}/payment", 'Customer/Invoices/Payment'],
        ['/customer/settings', 'Customer/Settings/Index'],
    ];

    foreach ($cases as [$url, $component]) {
        $response = $this->get($url);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component($component));
    }
});

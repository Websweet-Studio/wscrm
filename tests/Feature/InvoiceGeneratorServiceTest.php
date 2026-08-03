<?php

use App\Models\Order;
use App\Services\InvoiceGeneratorService;

it('generates renewal invoice with order discount and loyalty discount combined', function () {
    // Order dengan total_amount stale (= subtotal) seperti order lama
    $order = Order::factory()->active()->create([
        'customer_id' => \App\Models\Customer::factory(),
        'billing_cycle' => 'monthly',
        'auto_renew' => true,
        'domain_name' => 'example.com',
        'total_amount' => 638250,
        'discount_amount' => 83250,
        'expires_at' => now()->addDays(10),
        'created_at' => now()->subMonths(13), // memenuhi syarat loyalty 5%
    ]);

    $order->orderItems()->create([
        'item_type' => 'hosting',
        'item_id' => 1,
        'quantity' => 1,
        'price' => 638250,
    ]);

    $generated = (new InvoiceGeneratorService)->generateRenewalInvoices(30);

    expect($generated)->toBe(1);

    $invoice = $order->invoices()->first();
    $loyalty = 555000 * 0.05; // 5% dari net (subtotal - diskon order)

    expect($invoice)->not->toBeNull()
        ->and((float) $invoice->amount)->toBe(638250.0)
        ->and((float) $invoice->discount)->toBe(83250.0 + $loyalty)
        ->and((float) $invoice->final_amount)->toBe(555000.0 - $loyalty);
});

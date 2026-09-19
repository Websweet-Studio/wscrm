<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ServiceRenewalService;
use Illuminate\Support\Facades\Mail;

function renewalTestOrder(array $overrides = []): Order
{
    return Order::factory()->active()->create(array_merge([
        'customer_id' => Customer::factory(),
        'order_type' => 'hosting',
        // service_type ditulis eksplisit: OrderFactory memilih acak (hosting/domain)
        // sehingga catatan invoice ("... hosting service expiring on ...") jadi flaky.
        'service_type' => 'hosting',
        'domain_name' => 'kai.web.id',
        'billing_cycle' => 'annually',
        'status' => 'active',
        'total_amount' => 150000,
        'discount_amount' => 0,
        'expires_at' => '2026-10-03',
        'auto_renew' => true,
        'created_at' => now()->subMonths(13),
    ], $overrides));
}

it('extends the service, all items, and marks the existing renewal invoice as paid', function () {
    $order = renewalTestOrder();
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 3, 'quantity' => 1, 'price' => 130000, 'expires_at' => '2026-10-03', 'status' => 'active']);
    $order->orderItems()->create(['item_type' => 'domain', 'item_id' => 20, 'quantity' => 1, 'price' => 55000, 'expires_at' => '2026-10-03', 'status' => 'active']);

    $invoice = Invoice::create([
        'customer_id' => $order->customer_id,
        'order_id' => $order->id,
        'invoice_number' => 'INV-2026-08-0002',
        'invoice_type' => 'renewal',
        'amount' => 185000,
        'discount' => 15000,
        'issue_date' => '2026-08-04',
        'due_date' => '2026-09-26',
        'status' => 'pending',
        'billing_cycle' => 'annually',
        'notes' => 'Renewal invoice for kai.web.id',
    ]);

    $result = app(ServiceRenewalService::class)->renew($order, [
        'years' => 1,
        'mark_paid' => true,
        'paid_at' => '2026-09-18',
    ]);

    expect($result['old_expiry'])->toBe('2026-10-03')
        ->and($result['new_expiry'])->toBe('2027-10-03')
        ->and($result['items_updated'])->toBe(2)
        ->and($result['invoice_created'])->toBeFalse()
        ->and($result['invoice_status'])->toBe('paid')
        ->and($result['invoice_number'])->toBe('INV-2026-08-0002')
        ->and($result['net'])->toBe(170000.0);

    expect($order->fresh()->expires_at->toDateString())->toBe('2027-10-03');
    expect(OrderItem::where('order_id', $order->id)->pluck('expires_at')->map->toDateString()->all())
        ->toBe(['2027-10-03', '2027-10-03']);
    expect($invoice->fresh()->status)->toBe('paid');
    expect($invoice->fresh()->paid_at->toDateTimeString())->toBe('2026-09-18 00:00:00');
});

it('creates a renewal invoice without sending email when none exists', function () {
    Mail::fake();

    $order = renewalTestOrder([
        'total_amount' => 638250,
        'discount_amount' => 83250,
    ]);
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 1, 'quantity' => 1, 'price' => 638250, 'expires_at' => '2026-10-03', 'status' => 'active']);

    $result = app(ServiceRenewalService::class)->renew($order, [
        'create_invoice' => true,
        'mark_paid' => true,
        'paid_at' => '2026-09-18',
    ]);

    $loyalty = (638250 - 83250) * 0.05;

    expect($result['invoice_created'])->toBeTrue()
        ->and($result['invoice_status'])->toBe('paid')
        ->and($result['amount'])->toBe(638250.0)
        ->and($result['discount'])->toBe(83250.0 + $loyalty)
        ->and($result['net'])->toBe(638250.0 - 83250.0 - $loyalty);

    $invoice = $order->invoices()->first();
    expect($invoice->invoice_type)->toBe('renewal');
    expect(str_starts_with((string) $invoice->invoice_number, 'INV-'))->toBeTrue();

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

it('warns instead of marking paid when no invoice exists and --invoice is not given', function () {
    $order = renewalTestOrder();
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 3, 'quantity' => 1, 'price' => 130000, 'expires_at' => '2026-10-03', 'status' => 'active']);

    $result = app(ServiceRenewalService::class)->renew($order, ['mark_paid' => true]);

    expect($result['invoice_created'])->toBeFalse()
        ->and($result['invoice_id'])->toBeNull()
        ->and($result['warnings'])->toHaveCount(1);

    expect($order->invoices()->count())->toBe(0);
    expect($order->fresh()->expires_at->toDateString())->toBe('2027-10-03');
});

it('does not change anything in dry-run mode', function () {
    $order = renewalTestOrder();
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 3, 'quantity' => 1, 'price' => 130000, 'expires_at' => '2026-10-03', 'status' => 'active']);

    $result = app(ServiceRenewalService::class)->renew($order, [
        'dry_run' => true,
        'create_invoice' => true,
        'mark_paid' => true,
    ]);

    expect($result['dry_run'])->toBeTrue()
        ->and($result['new_expiry'])->toBe('2027-10-03');

    expect($order->fresh()->expires_at->toDateString())->toBe('2026-10-03');
    expect(OrderItem::where('order_id', $order->id)->first()->expires_at->toDateString())->toBe('2026-10-03');
    expect($order->invoices()->count())->toBe(0);
});

it('resolves a service by domain name (case-insensitive) or by id', function () {
    $order = renewalTestOrder(['domain_name' => 'Kai.Web.ID']);
    $service = app(ServiceRenewalService::class);

    expect($service->resolve('kai.web.id')?->id)->toBe($order->id)
        ->and($service->resolve((string) $order->id)?->id)->toBe($order->id)
        ->and($service->resolve('tidak-ada.web.id'))->toBeNull();
});

it('refuses cancelled or terminated services from the artisan command unless --force is used', function () {
    $order = renewalTestOrder(['status' => 'terminated', 'domain_name' => 'mati.web.id']);
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 3, 'quantity' => 1, 'price' => 130000]);

    $this->artisan('service:renew', ['target' => 'mati.web.id'])->assertFailed();
    expect($order->fresh()->expires_at->toDateString())->toBe('2026-10-03');

    $this->artisan('service:renew', ['target' => 'mati.web.id', '--force' => true])->assertSuccessful();
    expect($order->fresh()->expires_at->toDateString())->toBe('2027-10-03');
});

it('runs the artisan command end to end for a paid renewal', function () {
    $order = renewalTestOrder(['domain_name' => 'pabrikper.com', 'expires_at' => '2026-10-10']);
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 2, 'quantity' => 1, 'price' => 100000, 'expires_at' => '2026-10-10', 'status' => 'pending']);
    $order->orderItems()->create(['item_type' => 'domain', 'item_id' => 9, 'quantity' => 1, 'price' => 225000, 'expires_at' => null, 'status' => 'pending']);

    $this->artisan('service:renew', [
        'target' => 'pabrikper.com',
        '--invoice' => true,
        '--paid' => true,
        '--paid-at' => '2026-09-19',
        '--activate-items' => true,
    ])->assertSuccessful();

    $order->refresh();
    expect($order->expires_at->toDateString())->toBe('2027-10-10');
    expect(OrderItem::where('order_id', $order->id)->where('status', 'active')->count())->toBe(2);
    expect($order->invoices()->where('status', 'paid')->count())->toBe(1);
});

it('can tidy up invoice and items only, without extending the expiry (--no-extend)', function () {
    Mail::fake();

    $order = renewalTestOrder(['domain_name' => 'pabrikper.com', 'expires_at' => '2027-10-10', 'total_amount' => 325000]);
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 2, 'quantity' => 1, 'price' => 100000, 'expires_at' => null, 'status' => 'pending']);
    $order->orderItems()->create(['item_type' => 'domain', 'item_id' => 9, 'quantity' => 1, 'price' => 225000, 'expires_at' => null, 'status' => 'pending']);

    $result = app(ServiceRenewalService::class)->renew($order, [
        'extend' => false,
        'create_invoice' => true,
        'mark_paid' => true,
        'activate_items' => true,
        'paid_at' => '2026-09-19',
    ]);

    expect($result['extended'])->toBeFalse()
        ->and($result['new_expiry'])->toBe('2027-10-10')
        ->and($result['invoice_created'])->toBeTrue()
        ->and($result['invoice_status'])->toBe('paid')
        ->and($result['items_stale_status'])->toBe(0);

    expect($order->fresh()->expires_at->toDateString())->toBe('2027-10-10');
    expect(OrderItem::where('order_id', $order->id)->whereNull('expires_at')->count())->toBe(0);
    expect(OrderItem::where('order_id', $order->id)->pluck('expires_at')->map->toDateString()->all())
        ->toBe(['2027-10-10', '2027-10-10']);
    expect($order->invoices()->first()->status)->toBe('paid');

    Mail::assertNothingQueued();
});

it('creates an invoice without loyalty discount when --no-discount is used', function () {
    Mail::fake();

    $order = renewalTestOrder(['domain_name' => 'penuh.web.id', 'total_amount' => 325000]);
    $order->orderItems()->create(['item_type' => 'hosting', 'item_id' => 2, 'quantity' => 1, 'price' => 325000, 'expires_at' => '2026-10-10', 'status' => 'active']);

    $result = app(ServiceRenewalService::class)->renew($order, [
        'create_invoice' => true,
        'no_discount' => true,
        'mark_paid' => true,
        'paid_at' => '2026-09-19',
    ]);

    expect($result['discount'])->toBe(0.0)
        ->and($result['net'])->toBe(325000.0);

    $invoice = $order->invoices()->first();
    expect((float) $invoice->discount)->toBe(0.0);
    expect((float) $invoice->amount)->toBe(325000.0);
    expect((string) $invoice->notes)->toContain('Renewal invoice for penuh.web.id - hosting service expiring on');
    expect((string) $invoice->notes)->not->toContain('  ');

    Mail::assertNothingQueued();

    $this->artisan('service:renew', [
        'target' => 'penuh.web.id',
        '--no-extend' => true,
        '--no-discount' => true,
        '--dry-run' => true,
    ])->assertSuccessful();
});

it('lists due services including those already past expiry', function () {
    renewalTestOrder(['domain_name' => 'lewat.web.id', 'expires_at' => now()->subDays(7)]);
    renewalTestOrder(['domain_name' => 'segera.web.id', 'expires_at' => now()->addDays(10)]);
    renewalTestOrder(['domain_name' => 'jauh.web.id', 'expires_at' => now()->addDays(300)]);

    $rows = app(ServiceRenewalService::class)->due(60);

    expect($rows->pluck('domain')->all())->toBe(['lewat.web.id', 'segera.web.id']);
    expect($rows->first()['days_left'])->toBe(-7);

    $this->artisan('service:due', ['--days' => 60])->assertSuccessful();
    $this->artisan('service:due', ['--days' => 60, '--json' => true])->assertSuccessful();
});

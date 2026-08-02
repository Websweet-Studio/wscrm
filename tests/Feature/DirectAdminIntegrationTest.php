<?php

use App\Models\Customer;
use App\Models\DirectAdminSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'super_admin']);
    $this->customer = Customer::factory()->create();

    DirectAdminSetting::setValue('scheme', 'https');
    DirectAdminSetting::setValue('host', 'da.example.com');
    DirectAdminSetting::setValue('port', '2222');
    DirectAdminSetting::setValue('username', 'admin');
    DirectAdminSetting::setValue('password', 'secret');
    DirectAdminSetting::setValue('verify_ssl', '0');
});

it('renders directadmin index page when not configured', function () {
    DirectAdminSetting::query()->delete();

    $this->actingAs($this->user)
        ->get('/admin/directadmin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/DirectAdmin/Index'));
});

it('lists accounts and links orders by domain', function () {
    Order::factory()->create([
        'customer_id' => $this->customer->id,
        'domain_name' => 'example.com',
        'service_type' => 'hosting',
        'status' => 'active',
    ]);

    Http::fake([
        '*/CMD_API_SELECT_USERS' => Http::response('list[]=user1&list[]=user2'),
        '*/CMD_API_SHOW_USER_CONFIG*' => Http::response('error=0&username=user1&domain=example.com&email=a@example.com&package=basic&suspended=no'),
    ]);

    $this->actingAs($this->user)
        ->get('/admin/directadmin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/DirectAdmin/Index')
            ->has('accounts', 2)
            ->where('accounts.0.domain', 'example.com')
        );
});

it('suspends an account and updates the linked order', function () {
    $order = Order::factory()->create([
        'customer_id' => $this->customer->id,
        'domain_name' => 'example.com',
        'service_type' => 'hosting',
        'status' => 'active',
    ]);

    Http::fake([
        '*/CMD_API_MODIFY_USER' => Http::response('error=0&text=Suspended'),
        '*/CMD_API_SHOW_USER_CONFIG*' => Http::response('error=0&username=user1&domain=example.com&suspended=no'),
    ]);

    $this->actingAs($this->user)
        ->post('/admin/directadmin/accounts/user1/suspend')
        ->assertRedirect();

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'suspended']);
});

it('unsuspends an account and activates the linked order', function () {
    $order = Order::factory()->create([
        'customer_id' => $this->customer->id,
        'domain_name' => 'example.com',
        'service_type' => 'hosting',
        'status' => 'suspended',
    ]);

    Http::fake([
        '*/CMD_API_MODIFY_USER' => Http::response('error=0&text=Unsuspended'),
        '*/CMD_API_SHOW_USER_CONFIG*' => Http::response('error=0&username=user1&domain=example.com&suspended=yes'),
    ]);

    $this->actingAs($this->user)
        ->post('/admin/directadmin/accounts/user1/unsuspend')
        ->assertRedirect();

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'active']);
});

it('keeps existing password when saving settings with blank password', function () {
    Http::fake([
        '*/CMD_API_SHOW_USER_CONFIG*' => Http::response('error=0&text=ok'),
    ]);

    $this->actingAs($this->user)
        ->post('/admin/directadmin/settings', [
            'scheme' => 'http',
            'host' => 'da2.example.com',
            'port' => 2222,
            'username' => 'admin',
            'password' => '',
            'verify_ssl' => false,
        ])
        ->assertRedirect();

    expect(DirectAdminSetting::getValue('password'))->toBe('secret')
        ->and(DirectAdminSetting::getValue('host'))->toBe('da2.example.com');
});

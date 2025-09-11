<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access orders page without JavaScript errors', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/admin/orders');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Admin/Orders/Index')
        ->has('orders')
        ->has('customers')
        ->has('hostingPlans')
        ->has('domainPrices')
        ->has('servicePlans')
    );
});

it('can access orders page with view parameter', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/admin/orders?view=services');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Admin/Orders/Index')
        ->where('view', 'services')
    );
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/admin/orders');

    $response->assertRedirect('/login');
});

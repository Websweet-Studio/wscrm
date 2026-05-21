<?php

use App\Models\User;
use Illuminate\Support\Carbon;

test('admin services page is accessible for verified admin user', function () {
    $user = User::factory()->create([
        'role' => 'super_admin',
        'email_verified_at' => Carbon::now(),
    ]);

    $this->actingAs($user);

    $response = $this->get('/admin/services?status=active');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Admin/Orders/Index'));
});


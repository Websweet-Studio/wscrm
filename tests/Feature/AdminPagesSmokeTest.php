<?php

use App\Models\User;
use Illuminate\Support\Carbon;

test('admin pages can be opened by verified admin', function (string $url, string $component) {
    $user = User::factory()->create([
        'role' => 'super_admin',
        'email_verified_at' => Carbon::now(),
    ]);

    $this->actingAs($user);

    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component($component));
})->with(function () {
    return [
        ['/dashboard', 'Dashboard'],
        ['/admin/customers', 'Admin/Customers/Index'],
        ['/admin/orders', 'Admin/Orders/Index'],
        ['/admin/services', 'Admin/Orders/Index'],
        ['/admin/invoices', 'Admin/Invoices/Index'],
        ['/admin/domain-prices', 'Admin/DomainPrices/Index'],
        ['/admin/hosting-plans', 'Admin/HostingPlans/Index'],
        ['/admin/payments', 'Admin/Banks/Index'],
        ['/admin/blog', 'Admin/Blog/Index'],
        ['/admin/service-plans', 'Admin/ServicePlans/Index'],
        ['/admin/employees', 'Admin/Employees/Index'],
        ['/admin/tasks', 'Admin/Tasks/Index'],
        ['/admin/task-categories', 'Admin/TaskCategories/Index'],
        ['/admin/branding', 'Admin/Branding'],
        ['/admin/database', 'Admin/Database'],
        ['/admin/bulk-pricing', 'Admin/BulkPricing/Index'],
        ['/admin/expenses', 'Admin/Expenses/Index'],
    ];
});

<?php

use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\BulkPricingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\DemoCategoryController;
use App\Http\Controllers\Admin\DemoPackageController;
use App\Http\Controllers\Admin\DemoWebsiteController as AdminDemoWebsiteController;
use App\Http\Controllers\Admin\DomainPriceController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\HostingPlanController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentAccountController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TaskCategoryController;
use App\Http\Controllers\Admin\ServicePlanController;
use App\Http\Controllers\Admin\UserCredentialController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['admin.auth', 'auth', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::delete('customers/bulk', [CustomerController::class, 'bulkDestroy'])->name('customers.bulk-destroy');
    Route::post('customers/{customer}/welcome-email', [CustomerController::class, 'sendWelcomeEmail'])->name('customers.welcome-email');
    Route::post('customers/{customer}/resend-password', [CustomerController::class, 'resendPassword'])->name('customers.resend-password');
    Route::delete('employees/bulk', [EmployeeController::class, 'bulkDestroy'])->name('employees.bulk-destroy');
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
    Route::delete('orders/bulk', [OrderController::class, 'bulkDestroy'])->name('orders.bulk-destroy');
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('orders/{order}/simulate-upgrade-downgrade', [OrderController::class, 'simulateUpgradeDowngrade'])->name('orders.simulate-upgrade-downgrade');
    Route::post('orders/{order}/process-upgrade-downgrade', [OrderController::class, 'processUpgradeDowngrade'])->name('orders.process-upgrade-downgrade');

    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('services/{id}', [ServiceController::class, 'show'])->name('services.show');
    Route::delete('service-plans/bulk', [ServicePlanController::class, 'bulkDestroy'])->name('service-plans.bulk-destroy');
    Route::resource('service-plans', ServicePlanController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::patch('invoices/bulk/mark-paid', [InvoiceController::class, 'bulkMarkAsPaid'])->name('invoices.bulk-mark-paid');
    Route::delete('invoices/bulk', [InvoiceController::class, 'bulkDestroy'])->name('invoices.bulk-destroy');
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'sendInvoice'])->name('invoices.send');
    Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::post('invoices/generate-renewals', [InvoiceController::class, 'generateRenewalInvoices'])->name('invoices.generate-renewals');
    Route::delete('domain-prices/bulk', [DomainPriceController::class, 'bulkDestroy'])->name('domain-prices.bulk-destroy');
    Route::resource('domain-prices', DomainPriceController::class);
    Route::delete('hosting-plans/bulk', [HostingPlanController::class, 'bulkDestroy'])->name('hosting-plans.bulk-destroy');
    Route::resource('hosting-plans', HostingPlanController::class);
    Route::get('bulk-pricing', [BulkPricingController::class, 'index'])->name('bulk-pricing.index');
    Route::post('bulk-pricing/simulate', [BulkPricingController::class, 'simulate'])->name('bulk-pricing.simulate');
    Route::post('bulk-pricing/apply', [BulkPricingController::class, 'apply'])->name('bulk-pricing.apply');
    Route::post('bulk-pricing/save-config', [BulkPricingController::class, 'saveConfig'])->name('bulk-pricing.save-config');
    Route::get('bulk-pricing/load-config/{id}', [BulkPricingController::class, 'loadConfig'])->name('bulk-pricing.load-config');
    Route::delete('bulk-pricing/delete-config/{id}', [BulkPricingController::class, 'deleteConfig'])->name('bulk-pricing.delete-config');
    Route::get('banks', fn () => redirect()->route('admin.payments.index'))->name('banks.redirect');

    Route::get('payments', [PaymentAccountController::class, 'index'])->name('payments.index');
    Route::post('payments', [PaymentAccountController::class, 'store'])->name('payments.store');
    Route::put('payments/{payment}', [PaymentAccountController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}', [PaymentAccountController::class, 'destroy'])->name('payments.destroy');
    Route::patch('payments/{payment}/toggle-status', [PaymentAccountController::class, 'toggleStatus'])->name('payments.toggle-status');

    // Blog Management
    Route::resource('blog', BlogController::class);
    Route::patch('blog/{blog}/toggle-featured', [BlogController::class, 'toggleFeatured'])->name('blog.toggle-featured');
    Route::patch('blog/{blog}/toggle-pinned', [BlogController::class, 'togglePinned'])->name('blog.toggle-pinned');

    // Expense Management - Restricted to Super Admin only
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy'])->middleware('super_admin');

    // Customer Impersonation
    Route::post('impersonate/{customer}', [ImpersonateController::class, 'impersonate'])->name('impersonate');
    Route::post('stop-impersonation', [ImpersonateController::class, 'stopImpersonation'])->name('stop-impersonation');

    // User Credential Management
    Route::post('users/{user}/send-credentials', [UserCredentialController::class, 'sendCredentials'])->name('users.send-credentials');

    // Tasks Management
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('task-categories', TaskCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Branding Settings
    Route::middleware('no.cache')->group(function () {
        Route::get('branding', [BrandingController::class, 'index'])->name('branding.index');
        Route::patch('branding', [BrandingController::class, 'update'])->name('branding.update');
        Route::post('branding/upload-image', [BrandingController::class, 'uploadImage'])->name('branding.upload-image');
        Route::delete('branding/delete-image', [BrandingController::class, 'deleteImage'])->name('branding.delete-image');
    });

    // Database Export/Import
    Route::get('database', [DatabaseController::class, 'index'])->name('database.index');
    Route::get('database/export', [DatabaseController::class, 'export'])->name('database.export');
    Route::post('database/import', [DatabaseController::class, 'import'])->name('database.import');
    Route::post('database/clear', [DatabaseController::class, 'clear'])->name('database.clear');

    // Demo Website Management
    Route::resource('demo-websites', AdminDemoWebsiteController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('demo-websites/{demoWebsite}/toggle-status', [AdminDemoWebsiteController::class, 'toggleStatus'])->name('demo-websites.toggle-status');
    Route::resource('demo-categories', DemoCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('demo-categories/{demoCategory}/toggle-status', [DemoCategoryController::class, 'toggleStatus'])->name('demo-categories.toggle-status');
    Route::resource('demo-packages', DemoPackageController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('demo-packages/{demoPackage}/toggle-status', [DemoPackageController::class, 'toggleStatus'])->name('demo-packages.toggle-status');
});

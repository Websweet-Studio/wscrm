<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\BulkPricingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DomainPriceController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\HostingPlanController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\ServicePlanController;
use App\Http\Controllers\Admin\UserCredentialController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['admin.auth', 'auth', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('orders/{order}/simulate-upgrade-downgrade', [OrderController::class, 'simulateUpgradeDowngrade'])->name('orders.simulate-upgrade-downgrade');
    Route::post('orders/{order}/process-upgrade-downgrade', [OrderController::class, 'processUpgradeDowngrade'])->name('orders.process-upgrade-downgrade');

    // Legacy service routes redirect to orders with services view
    Route::get('services', function () {
        return redirect()->route('admin.orders.index', ['view' => 'services']);
    })->name('services.index');
    Route::get('services/{id}', function ($id) {
        return redirect()->route('admin.orders.show', $id);
    })->name('services.show');
    Route::resource('service-plans', ServicePlanController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show', 'store', 'update']);
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
    Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::post('invoices/generate-renewals', [InvoiceController::class, 'generateRenewalInvoices'])->name('invoices.generate-renewals');
    Route::resource('domain-prices', DomainPriceController::class);
    Route::resource('hosting-plans', HostingPlanController::class);
    Route::get('bulk-pricing', [BulkPricingController::class, 'index'])->name('bulk-pricing.index');
    Route::post('bulk-pricing/simulate', [BulkPricingController::class, 'simulate'])->name('bulk-pricing.simulate');
    Route::post('bulk-pricing/apply', [BulkPricingController::class, 'apply'])->name('bulk-pricing.apply');
    Route::post('bulk-pricing/save-config', [BulkPricingController::class, 'saveConfig'])->name('bulk-pricing.save-config');
    Route::get('bulk-pricing/load-config/{id}', [BulkPricingController::class, 'loadConfig'])->name('bulk-pricing.load-config');
    Route::delete('bulk-pricing/delete-config/{id}', [BulkPricingController::class, 'deleteConfig'])->name('bulk-pricing.delete-config');
    Route::resource('banks', BankController::class);
    Route::patch('banks/{bank}/toggle-status', [BankController::class, 'toggleStatus'])->name('banks.toggle-status');

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
});

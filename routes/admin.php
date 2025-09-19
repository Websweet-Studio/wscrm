<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BulkPricingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DomainPriceController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\HostingPlanController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ServicePlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

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

    // Expense Management
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');

    // Customer Impersonation
    Route::post('impersonate/{customer}', [ImpersonateController::class, 'impersonate'])->name('impersonate');
    Route::post('stop-impersonation', [ImpersonateController::class, 'stopImpersonation'])->name('stop-impersonation');
});

<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DomainPriceController;
use App\Http\Controllers\Admin\HostingPlanController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ServicePlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('orders/create-service', [OrderController::class, 'createService'])->name('orders.create-service');

    // Legacy service routes redirect to orders with services view
    Route::get('services', function () {
        return redirect()->route('admin.orders.index', ['view' => 'services']);
    })->name('services.index');
    Route::get('services/{id}', function ($id) {
        return redirect()->route('admin.orders.show', $id);
    })->name('services.show');
    Route::resource('service-plans', ServicePlanController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show', 'store', 'update']);
    Route::post('invoices/generate-renewals', [InvoiceController::class, 'generateRenewalInvoices'])->name('invoices.generate-renewals');
    Route::resource('domain-prices', DomainPriceController::class);
    Route::resource('hosting-plans', HostingPlanController::class);
    Route::resource('banks', BankController::class);
    Route::patch('banks/{bank}/toggle-status', [BankController::class, 'toggleStatus'])->name('banks.toggle-status');
});

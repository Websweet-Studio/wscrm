<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DomainPriceController;
use App\Http\Controllers\Admin\HostingPlanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServicePlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('services', ServiceController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('service-plans', ServicePlanController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('domain-prices', DomainPriceController::class);
    Route::resource('hosting-plans', HostingPlanController::class);
    Route::resource('banks', BankController::class);
    Route::patch('banks/{bank}/toggle-status', [BankController::class, 'toggleStatus'])->name('banks.toggle-status');
});

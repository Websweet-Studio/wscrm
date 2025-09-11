<?php

use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Customer\Auth\LoginController;
use App\Http\Controllers\Customer\Auth\RegisterController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\DomainController;
use App\Http\Controllers\Customer\HostingController;
use App\Http\Controllers\Customer\SettingsController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store']);
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store']);
    });

    Route::middleware('auth:customer')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('hosting')->name('hosting.')->group(function () {
            Route::get('/', [HostingController::class, 'index'])->name('index');
            Route::get('/{hostingPlan}', [HostingController::class, 'show'])->name('show');
        });

        Route::prefix('domains')->name('domains.')->group(function () {
            Route::get('/', [DomainController::class, 'index'])->name('index');
            Route::get('/search', [DomainController::class, 'search'])->name('search');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        });

        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        });

        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Customer\InvoiceController::class, 'index'])->name('index');
            Route::get('/{invoice}', [\App\Http\Controllers\Customer\InvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/payment', [\App\Http\Controllers\Customer\InvoiceController::class, 'payment'])->name('payment');
            Route::post('/{invoice}/process-payment', [\App\Http\Controllers\Customer\InvoiceController::class, 'processPayment'])->name('process-payment');
            Route::post('/{invoice}/confirm-payment', [\App\Http\Controllers\Customer\InvoiceController::class, 'confirmPayment'])->name('confirm-payment');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::patch('/profile', [SettingsController::class, 'updateProfile'])->name('update-profile');
            Route::patch('/password', [SettingsController::class, 'updatePassword'])->name('update-password');
        });
        
        // Stop Impersonation (available in customer area)
        Route::post('/stop-impersonation', [ImpersonateController::class, 'stopImpersonation'])->name('stop-impersonation');
    });
});

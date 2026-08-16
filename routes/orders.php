<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Route order & service customer (dahulu whmcs.php — nama tidak mencerminkan isi).
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    });

    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    });
});

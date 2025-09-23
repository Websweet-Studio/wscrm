<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('CustomerWelcome');
})->name('home');

// Public hosting and domain pages (accessible without login)
Route::get('/hosting', [App\Http\Controllers\HostingPlanController::class, 'publicIndex'])->name('public.hosting.index');
Route::get('/domains', [App\Http\Controllers\DomainPriceController::class, 'publicIndex'])->name('public.domains.index');
Route::get('/domains/search', [App\Http\Controllers\DomainPriceController::class, 'publicSearch'])->name('public.domains.search');

// Public blog pages (accessible without login)
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blogPost:slug}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', [App\Http\Controllers\BlogController::class, 'category'])->name('blog.category');

// Services catalog (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/services', [App\Http\Controllers\ServicePlanController::class, 'index'])->name('services.index');
    Route::get('/services/{servicePlan}', [App\Http\Controllers\ServicePlanController::class, 'show'])->name('service-plans.show');
});

// API endpoints
Route::post('/api/domains/availability', [App\Http\Controllers\DomainPriceController::class, 'checkAvailability'])
    ->name('api.domains.availability');

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('admin', function () {
    return redirect('/dashboard');
})->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/whmcs.php';
require __DIR__.'/customer.php';
require __DIR__.'/admin.php';

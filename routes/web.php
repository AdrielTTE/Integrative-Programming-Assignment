<?php

use App\Http\Controllers\AdminControllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PackageController;

// Admin Routes
Route::match(['get', 'post'], '/admin/dashboard', [DashboardController::class, 'dashboard'])
    ->name('adminDashboard');


Route::get('/', function () {
    return view('welcome');
});

// Customer Routes (if needed later)
// Route::prefix('customer')->group(function () {
//     Route::get('/dashboard', [CustomerDashboardController::class, 'index']);
// });

// --- Public Routes (tracking) ---
Route::get('/track', [PackageController::class, 'track'])->name('packages.track');
Route::post('/track', [PackageController::class, 'track'])->name('packages.track.submit');

// --- Protected Routes (temporarily without auth middleware) ---
Route::group([], function () {
    Route::get('/dashboard', [PackageController::class, 'dashboard'])->name('packages.dashboard');
    Route::resource('packages', PackageController::class);

    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/search/advanced', [PackageController::class, 'search'])->name('search');
        Route::post('/search/advanced', [PackageController::class, 'search'])->name('search.submit');
        Route::post('/bulk-update', [PackageController::class, 'bulkUpdate'])->name('bulk.update');
        Route::get('/reports/generate', [PackageController::class, 'generateReport'])->name('reports.generate');
        Route::post('/reports/generate', [PackageController::class, 'generateReport'])->name('reports.generate.submit');
        Route::patch('/{package}/status', [PackageController::class, 'updateStatus'])->name('status.update');
    });
});

Route::get('/', function () {
    return view('layouts.customerLayout');
})->name('customer.home');


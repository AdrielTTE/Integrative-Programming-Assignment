<?php
use App\Http\Controllers\AdminControllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('adminDashboard');
});

// Customer Routes
/*Route::prefix('customer')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index']);
});*/




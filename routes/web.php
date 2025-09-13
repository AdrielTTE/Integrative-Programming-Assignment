<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminControllers\DashboardController;
use App\Http\Controllers\Web\PackageController;

Route::get('/track', [PackageController::class, 'track'])->name('packages.track');
Route::post('/track', [PackageController::class, 'track'])->name('packages.track.submit');

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('customer')->group(function () {
Route::get('login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('login', [CustomerAuthController::class, 'login']);
Route::get('register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
Route::post('register', [CustomerAuthController::class, 'store'])->name('customer.register.submit');

Route::middleware('auth')->group(function () {
    Route::get('/customer/dashboard', fn() => view('customer.dashboard'))->name('customer.dashboard');
});
});


Route::prefix('admin')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('admin.register');
    Route::post('register', [AdminAuthController::class, 'store'])->name('admin.register.submit');


   Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
});
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DriverAuthController;
use App\Http\Controllers\AdminControllers\DashboardController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\ProofController as WebProofController;
use App\Http\Controllers\AdminControllers\ProofManagementController;
use App\Http\Controllers\Web\SearchController as WebSearchController;
use App\Http\Controllers\AdminControllers\SearchController as AdminSearchController;
use App\Http\Controllers\CustomerControllers\CustomerNotificationController;

Route::get('/track', [PackageController::class, 'track'])->name('packages.track');
Route::post('/track', [PackageController::class, 'track'])->name('packages.track.submit');

Route::get('/', function () { return view('welcome'); });

Route::prefix('customer')->group(function () {
Route::get('login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('login', [CustomerAuthController::class, 'login']);
Route::get('register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
Route::post('register', [CustomerAuthController::class, 'store'])->name('customer.register.submit');

Route::middleware(['auth','customer'])->group(function () {
    Route::get('/dashboard', fn() => view('customer.dashboard'))->name('customer.dashboard');
});

Route::get('notification', [CustomerNotificationController::class, 'notification'])
    ->name('customer.notification');

});


Route::prefix('admin')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('admin.register');
    Route::post('register', [AdminAuthController::class, 'store'])->name('admin.register.submit');


   Route::middleware(['auth','admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/proofs', [ProofManagementController::class, 'index'])->name('admin.proof.index');
    Route::get('/proofs/history', [ProofManagementController::class, 'history'])->name('admin.proof.history');
    Route::get('/proofs/{proofId}', [ProofManagementController::class, 'show'])->name('admin.proof.show');
    Route::post('/proofs/{proofId}/update-status', [ProofManagementController::class, 'updateStatus'])->name('admin.proof.updateStatus');
    Route::get('/search', [AdminSearchController::class, 'search'])->name('admin.search');
    Route::post('/search/bulk', [AdminSearchController::class, 'bulkAction'])->name('admin.search.bulk');
});
});

//Driver routes @Qi Yao put your page here
Route::prefix('driver')->group(function () {
    Route::get('login', [DriverAuthController::class, 'showLoginForm'])->name('driver.login');
    Route::post('login', [DriverAuthController::class, 'login'])->name('driver.login');
    Route::get('register', [DriverAuthController::class, 'showRegisterForm'])->name('driver.register');
    Route::post('register', [DriverAuthController::class, 'store'])->name('driver.register.submit');

//Update this
   Route::middleware(['auth','driver'])->group(function () {
    Route::get('/package/manage', [DriverPackageController::class, 'packageManagement'])->name('driver.packageManagement');
});
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// Customer Routes
Route::group([], function() {
    Route::get('/home', function () { return redirect()->route('customer.search'); })->name('customer.home');
    Route::get('/my-packages/search', [WebSearchController::class, 'search'])->name('customer.search');
    Route::get('/my-packages/{packageId}', [PackageController::class, 'show'])->name('customer.package.show');
    Route::post('/proofs/{proofId}/report', [WebProofController::class, 'report'])->name('customer.proof.report');
    Route::get('/my-proofs', [WebProofController::class, 'history'])->name('customer.proof.history');
});

// General Package Routes
Route::group([], function () {
    Route::get('/dashboard', [PackageController::class, 'dashboard'])->name('packages.dashboard');
    Route::resource('packages', PackageController::class)->except(['show']);
    Route::post('/packages/bulk-update', [PackageController::class, 'bulkUpdate'])->name('packages.bulk.update');
    Route::get('/packages/reports/generate', [PackageController::class, 'generateReport'])->name('packages.reports.generate');
});

require __DIR__ . '/auth.php';


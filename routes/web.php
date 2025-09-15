<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DriverAuthController;
use App\Http\Controllers\AdminControllers\DashboardController;
use App\Http\Controllers\AdminControllers\ProofManagementController;
use App\Http\Controllers\AdminControllers\SearchController as AdminSearchController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\ProofController as WebProofController;
use App\Http\Controllers\Web\SearchController as WebSearchController;
use App\Http\Controllers\CustomerControllers\CustomerNotificationController;

use App\Http\Controllers\DriverControllers\DriverDashboardController;

use App\Http\Controllers\DriverControllers\AssignedPackageController;


/*Public Routes (No Login Required)*/
Route::get('/', function () {
    return view('welcome');
});

// Public package tracking page
Route::get('/track', [PackageController::class, 'track'])->name('packages.track');
Route::post('/track', [PackageController::class, 'track'])->name('packages.track.submit');


/*Customer Routes*/
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes for customer login & registration
    Route::get('login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [CustomerAuthController::class, 'login']);
    Route::get('register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [CustomerAuthController::class, 'store'])->name('register.submit');

    Route::middleware(['auth', 'customer'])->group(function () {
        Route::get('/dashboard', fn() => redirect()->route('customer.search'))->name('dashboard');
        Route::get('/home', fn() => redirect()->route('customer.search'))->name('home');

        Route::get('notification', [CustomerNotificationController::class, 'notification'])->name('notification');

        // Search, Package, and Proof routes are now correctly protected
        Route::get('/my-packages/search', [WebSearchController::class, 'search'])->name('search');
        Route::get('/my-packages/{packageId}', [PackageController::class, 'show'])->name('package.show');
        Route::get('/my-proofs', [WebProofController::class, 'history'])->name('proof.history');
        Route::post('/proofs/{proofId}/report', [WebProofController::class, 'report'])->name('proof.report');
    });
});


/*Admin Routes*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes for admin login & registration
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AdminAuthController::class, 'store'])->name('register.submit');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // Proof Management
        Route::get('/proofs', [ProofManagementController::class, 'index'])->name('proof.index');
        Route::get('/proofs/history', [ProofManagementController::class, 'history'])->name('proof.history');
        Route::get('/proofs/{proofId}', [ProofManagementController::class, 'show'])->name('proof.show');
        Route::post('/proofs/{proofId}/update-status', [ProofManagementController::class, 'updateStatus'])->name('proof.updateStatus');

        // Admin Search
        Route::get('/search', [AdminSearchController::class, 'search'])->name('search');
        Route::post('/search/bulk', [AdminSearchController::class, 'bulkAction'])->name('search.bulk');
    });
});

/*Driver Routes*/
Route::prefix('driver')->name('driver.')->group(function () {
    Route::get('login', [DriverAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [DriverAuthController::class, 'login']);
    Route::get('register', [DriverAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [DriverAuthController::class, 'store'])->name('register.submit');

//Update this
    Route::middleware(['auth','driver'])->group(function () {


    Route::get('/dashboard', [DriverDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/packages', [AssignedPackageController::class, 'assignedPackages'])->name('assignedPackages');
});
});

/* General Authenticated Routes (For all logged-in users)*/
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
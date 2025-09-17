<?php

use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------------
// CONTROLLER IMPORTS WITH ALIASES TO PREVENT NAMING CONFLICTS
// ---------------------------------------------------------------------------------
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DriverAuthController;

// Admin Controllers
use App\Http\Controllers\AdminControllers\DashboardController;
use App\Http\Controllers\AdminControllers\FeedbackController;
use App\Http\Controllers\AdminControllers\ProofManagementController;
use App\Http\Controllers\AdminControllers\SearchController as AdminSearchController;
// Note: AdminPackageController and PackageAssignmentController were missing, add them if they exist
// use App\Http\Controllers\AdminControllers\AdminPackageController;
// use App\Http\Controllers\AdminControllers\PackageAssignmentController;

// Customer Controllers
use App\Http\Controllers\CustomerControllers\CustomerNotificationController;
use App\Http\Controllers\CustomerControllers\PackageController as CustomerPackageController;
use App\Http\Controllers\CustomerControllers\TemporaryController;

// Driver Controllers
use App\Http\Controllers\DriverControllers\DriverDashboardController;
use App\Http\Controllers\DriverControllers\AssignedPackageController;

// General Web Controllers
use App\Http\Controllers\Web\PackageController as WebPackageController;
use App\Http\Controllers\Web\ProofController as WebProofController;
use App\Http\Controllers\Web\SearchController as WebSearchController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* ================================================================================= */
/*                            PUBLIC ROUTES (No Login Required)                      */
/* ================================================================================= */

Route::get('/', function () {
    return view('welcome');
});

/* ================================================================================= */
/*                                 CUSTOMER ROUTES                                   */
/* ================================================================================= */
Route::prefix('customer')->name('customer.')->group(function () {

    // --- Guest routes for customer login & registration ---
    Route::middleware('guest')->group(function() {
        Route::get('login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [CustomerAuthController::class, 'login']);
        Route::get('register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [CustomerAuthController::class, 'store'])->name('register.submit');
    });

    // --- Authenticated Customer Routes ---
    Route::middleware(['auth', 'customer'])->group(function () {

        Route::get('/dashboard', fn() => redirect()->route('customer.packages.index'))->name('dashboard');
        Route::get('/home', fn() => redirect()->route('customer.packages.index'))->name('home');

        // --- Package Management Routes (All correctly point to CustomerPackageController) ---
        Route::resource('packages', CustomerPackageController::class)
             ->parameters(['packages' => 'packageId']); // Ensures URLs use {packageId}

        // --- Other Custom Package Routes ---
        Route::post('/packages/undo', [CustomerPackageController::class, 'undo'])->name('packages.undo');
        // Note: The calculate-cost route was in your original file but may be missing from the controller. Add the method if needed.
        // Route::post('/packages/calculate-cost', [CustomerPackageController::class, 'calculateCost'])->name('packages.calculate-cost');

        // --- Other Customer-Specific Routes ---
        Route::get('/my-packages/search', [WebSearchController::class, 'search'])->name('search');
        Route::get('/my-proofs', [WebProofController::class, 'history'])->name('proof.history');
        Route::post('/proofs/{proofId}/report', [WebProofController::class, 'report'])->name('proof.report');
        Route::get('notification', [CustomerNotificationController::class, 'notification'])->name('notification');

        Route::get('/temporaryPage', [TemporaryController::class, 'temporaryPage'])->name('temporaryPage');

    });
});


/* ================================================================================= */
/*                                  ADMIN ROUTES                                     */
/* ================================================================================= */
Route::prefix('admin')->name('admin.')->group(function () {

    // --- Guest routes for admin login ---
    Route::middleware('guest')->group(function() {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login']);
        Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [AdminAuthController::class, 'store'])->name('register.submit');
    });

    // --- Authenticated Admin Routes ---
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // Note: Add 'use' statements for AdminPackageController and PackageAssignmentController if they exist.
        // Route::get('/assign-packages', [PackageAssignmentController::class, 'index'])->name('packages.assign');
        // Route::resource('packages', AdminPackageController::class); // Example of using a resource controller for admin

        // Proof Management
        Route::get('/proofs', [ProofManagementController::class, 'index'])->name('proof.index');
        Route::get('/proofs/history', [ProofManagementController::class, 'history'])->name('proof.history');
        Route::get('/proofs/{proofId}', [ProofManagementController::class, 'show'])->name('proof.show');
        Route::post('/proofs/{proofId}/update-status', [ProofManagementController::class, 'updateStatus'])->name('proof.updateStatus');

        // Admin Search & Feedback
        Route::get('/search', [AdminSearchController::class, 'search'])->name('search');
        Route::post('/search/bulk', [AdminSearchController::class, 'bulkAction'])->name('search.bulk');
        Route::get('/feedback', [FeedbackController::class, 'feedback'])->name('feedback');
    });
});


/* ================================================================================= */
/*                                  DRIVER ROUTES                                    */
/* ================================================================================= */
Route::prefix('driver')->name('driver.')->group(function () {

    // --- Guest routes for driver login ---
    Route::middleware('guest')->group(function() {
        Route::get('login', [DriverAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [DriverAuthController::class, 'login']);
        Route::get('register', [DriverAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [DriverAuthController::class, 'store'])->name('register.submit');
    });

    // --- Authenticated Driver Routes ---
    Route::middleware(['auth','driver'])->group(function () {
        Route::get('/dashboard', [DriverDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/packages', [AssignedPackageController::class, 'assignedPackages'])->name('assignedPackages');
    });
});


/* ================================================================================= */
/*                 GENERAL AUTHENTICATED ROUTES (For all logged-in users)            */
/* ================================================================================= */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// This file often contains the logout route and other authentication routes.
require __DIR__ . '/auth.php';

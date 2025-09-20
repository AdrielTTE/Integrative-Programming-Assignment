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
use App\Http\Controllers\AdminControllers\AdminPackageController; // ADD THIS
use App\Http\Controllers\AdminControllers\AdminAssignmentController;
use App\Http\Controllers\AdminControllers\AnnouncementController;

// Customer Controllers
use App\Http\Controllers\CustomerControllers\CustomerNotificationController;
use App\Http\Controllers\CustomerControllers\PackageController as CustomerPackageController;
use App\Http\Controllers\CustomerControllers\CustomerDashboardController;
use App\Http\Controllers\CustomerControllers\TemporaryController;
use App\Http\Controllers\CustomerControllers\FeedbackController as CustomerFeedbackController;

// Driver Controllers
use App\Http\Controllers\DriverControllers\DriverDashboardController;
use App\Http\Controllers\DriverControllers\AssignedPackageController;

use App\Http\Controllers\DriverControllers\DriverPackagesController;
use App\Http\Controllers\Api\DeliveryController; 
use App\Http\Controllers\DriverControllers\DeliveryHistoryController;




// General Web Controllers
use App\Http\Controllers\DriverControllers\DeliveryStatusController;
use App\Http\Controllers\Web\ProofController as WebProofController;
use App\Http\Controllers\Web\SearchController as WebSearchController;

use App\Http\Controllers\AdminControllers\PaymentController;
use App\Http\Controllers\AdminControllers\RefundController;

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
    Route::middleware('guest')->group(function () {
        Route::get('login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [CustomerAuthController::class, 'login']);
        Route::get('register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [CustomerAuthController::class, 'store'])->name('register.submit');
    });

    // --- Authenticated Customer Routes ---
    Route::middleware(['auth', 'customer'])->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/home', fn() => redirect()->route('customer.packages.index'))->name('home');

        // --- Package Management Routes ---
        Route::resource('packages', CustomerPackageController::class)
             ->parameters(['packages' => 'packageId']);

        Route::post('/packages/{packageId}/process', [CustomerPackageController::class, 'process'])
             ->name('packages.process');

        // --- Other Custom Package Routes ---
        Route::post('/packages/undo', [CustomerPackageController::class, 'undo'])->name('packages.undo');

        // --- Other Customer-Specific Routes ---
        Route::get('/my-packages/search', [WebSearchController::class, 'search'])->name('search');
        Route::get('/my-proofs', [WebProofController::class, 'history'])->name('proof.history');
        Route::post('/proofs/{proofId}/report', [WebProofController::class, 'report'])->name('proof.report');
        Route::get('notification', [CustomerNotificationController::class, 'notification'])->name('notification');

        Route::get('/temporaryPage', [TemporaryController::class, 'temporaryPage'])->name('temporaryPage');

        Route::get('/feedback', [CustomerFeedbackController::class, 'feedback'])->name('feedback');
        Route::post('/feedback', [CustomerFeedbackController::class, 'store'])->name('feedback.store');
        Route::post('/notifications/updateReadAt/{notification_id}', [CustomerNotificationController::class, 'updateReadAt'])->name('notifications.updateReadAt');
    });
});


/* ================================================================================= */
/*                                  ADMIN ROUTES                                     */
/* ================================================================================= */
Route::prefix('admin')->name('admin.')->group(function () {

    // --- Guest routes for admin login ---
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login']);
        Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [AdminAuthController::class, 'store'])->name('register.submit');
    });

    // --- Authenticated Admin Routes ---
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // Package Management
        Route::resource('packages', AdminPackageController::class)->parameters(['packages' => 'packageId']);
        Route::post('/packages/bulk-action', [AdminPackageController::class, 'bulkAction'])->name('packages.bulk');
        Route::get('/packages-export', [AdminPackageController::class, 'exportPackagesData'])->name('packages.export');
        Route::post('/packages-import-feedback', [AdminPackageController::class, 'importCustomerFeedback'])->name('packages.import.feedback');

        // Package Assignments
        Route::get('/package-assignments', [AdminAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/package-assignments/{packageId}/assign', [AdminAssignmentController::class, 'assign'])->name('assignments.assign');

        // Proof Management
        Route::get('/proofs', [ProofManagementController::class, 'index'])->name('proof.index');
        Route::get('/proofs/history', [ProofManagementController::class, 'history'])->name('proof.history');
        Route::get('/proofs/{proofId}', [ProofManagementController::class, 'show'])->name('proof.show');
        Route::post('/proofs/{proofId}/update-status', [ProofManagementController::class, 'updateStatus'])->name('proof.updateStatus');

        // Admin Search & Feedback
        Route::get('/search', [AdminSearchController::class, 'search'])->name('search');
        Route::post('/search/bulk', [AdminSearchController::class, 'bulkAction'])->name('search.bulk');
        Route::get('/feedback', [FeedbackController::class, 'feedback'])->name('feedback');

        // Payment Management
        Route::get('/payment', [PaymentController::class, 'index'])->name('payment');
        Route::post('/payment/report', [PaymentController::class, 'generateReport'])->name('payment.report');
        Route::get('/payment/{id}/invoice', [PaymentController::class, 'generateInvoice'])->name('payment.invoice');

        // Refund Management
        Route::get('/refunds', [RefundController::class, 'index'])->name('refunds');
        Route::post('/refunds/{id}/approve', [RefundController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{id}/reject', [RefundController::class, 'reject'])->name('refunds.reject');
        Route::post('/refunds/{id}/process', [RefundController::class, 'process'])->name('refunds.process');

        // Announcements and Notification
        Route::get('/announcement', [AnnouncementController::class, 'create'])->name('announcement.create');
Route::post('/announcement', [AnnouncementController::class, 'send'])->name('announcement.send');
    });

});


/* ================================================================================= */
/*                                  DRIVER ROUTES                                    */
/* ================================================================================= */
Route::prefix('driver')->name('driver.')->group(function () {

    // --- Guest routes for driver login ---
    Route::middleware('guest')->group(function () {
        Route::get('login', [DriverAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [DriverAuthController::class, 'login']);
        Route::get('register', [DriverAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [DriverAuthController::class, 'store'])->name('register.submit');
    });

    // --- Authenticated Driver Routes ---
    Route::middleware(['auth', 'driver'])->group(function () {
        Route::get('/dashboard', [DriverDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/packages', [AssignedPackageController::class, 'assignedPackages'])->name('assignedPackages');
    });

    Route::middleware(['auth', 'driver'])->group(function () {
        Route::get('/dashboard', [DriverDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/my-packages', [DriverPackagesController::class, 'index'])->name('packages.index');
    });

    Route::get('/package/{packageId}', [DeliveryController::class, 'getDeliveryPackageDetails'])->middleware('auth:sanctum');

    Route::get('/dashboard', [DriverDashboardController::class, 'dashboard'])->name('dashboard');

    
    Route::get('/my-packages', [DriverPackagesController::class, 'index'])->name('packages.index');

    
    Route::get('/update-status', [DeliveryStatusController::class, 'index'])->name('status.index');
    Route::post('/update-status/{packageId}', [DeliveryStatusController::class, 'update'])->name('status.update');

     Route::get('/dashboard', [DriverDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/my-packages', [DriverPackagesController::class, 'index'])->name('packages.index');
        Route::get('/update-status', [DeliveryStatusController::class, 'index'])->name('status.index');
        Route::post('/update-status/{packageId}', [DeliveryStatusController::class, 'update'])->name('status.update');

        // --- NEW DELIVERY HISTORY ROUTE ---
        Route::get('/delivery-history', [DeliveryHistoryController::class, 'index'])->name('history.index');
});


/* ================================================================================= */
/*                 GENERAL AUTHENTICATED ROUTES (For all logged-in users)            */
/* ================================================================================= */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Payment Management
    Route::get('/payment', [PaymentController::class, 'index'])->name('admin.payment');
    Route::post('/payment/report', [PaymentController::class, 'generateReport'])->name('admin.payment.report');
    Route::get('/payment/{id}/invoice', [PaymentController::class, 'generateInvoice'])->name('admin.payment.invoice');

    // Refund Management
    Route::get('/refunds', [RefundController::class, 'index'])->name('admin.refunds');
    Route::post('/refunds/{id}/approve', [RefundController::class, 'approve'])->name('admin.refunds.approve');
    Route::post('/refunds/{id}/reject', [RefundController::class, 'reject'])->name('admin.refunds.reject');
    Route::post('/refunds/{id}/process', [RefundController::class, 'process'])->name('admin.refunds.process');
});

// This file often contains the logout route and other authentication routes.
require __DIR__ . '/auth.php';

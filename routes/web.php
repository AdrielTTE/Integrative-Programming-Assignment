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
use App\Http\Controllers\Api\DriverController as ApiDriverController;

use App\Http\Controllers\DriverControllers\AssignedPackageController;

use App\Http\Controllers\DriverControllers\DriverPackagesController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\DriverControllers\DeliveryHistoryController;
use App\Http\Controllers\DriverControllers\ProofOfDeliveryController;

use App\Http\Controllers\DriverControllers\DriverProofController;

use App\Http\Controllers\CustomerControllers\CustomerPaymentController;
use App\Http\Controllers\AdminControllers\PaymentController;
use App\Http\Controllers\AdminControllers\RefundController;

// General Web Controllers
use App\Http\Controllers\DriverControllers\DeliveryStatusController;
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

    // Payment Routes
    Route::prefix('payment')->name('payment.')->group(function () {
        // New payment-first flow (MOVED FROM ADMIN SECTION)
        Route::post('/create-and-pay', [CustomerPaymentController::class, 'createAndPay'])
            ->name('createAndPay');
        Route::get('/session', [CustomerPaymentController::class, 'showSessionPayment'])
            ->name('showSessionPayment');
        Route::post('/session/process', [CustomerPaymentController::class, 'processSessionPayment'])
            ->name('processSessionPayment');
        
        // Make payment for an existing package
        Route::get('/package/{packageId}', [CustomerPaymentController::class, 'showPaymentPage'])
             ->name('make');
        Route::post('/package/{packageId}', [CustomerPaymentController::class, 'processPayment'])
             ->name('process');
        
        // Payment success page
        Route::get('/success/{paymentId}', [CustomerPaymentController::class, 'paymentSuccess'])
             ->name('success');
    });

    // Billing History
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/history', [CustomerPaymentController::class, 'billingHistory'])
             ->name('history');
        Route::get('/invoice/{paymentId}/download', [CustomerPaymentController::class, 'downloadInvoice'])
             ->name('invoice.download');
        Route::get('/receipt/{paymentId}', [CustomerPaymentController::class, 'generateReceipt'])
             ->name('receipt');
    });
    
    // Refund Routes
    Route::prefix('refund')->name('refund.')->group(function () {
        Route::get('/request/{paymentId}', [CustomerPaymentController::class, 'showRefundRequest'])
             ->name('request');
        Route::post('/request/{paymentId}', [CustomerPaymentController::class, 'submitRefund'])
             ->name('submit');
        Route::get('/status/{refundId}', [CustomerPaymentController::class, 'refundStatus'])
             ->name('status');
    });

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

        // Audit Log Routes
        Route::get('/audit-logs', [AdminPackageController::class, 'auditLogs'])->name('audit.logs');
        Route::get('/audit-logs/export', [AdminPackageController::class, 'exportAuditLogs'])->name('audit.export');

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
        Route::prefix('payment')->name('payment.')->group(function () {
            // New payment-first flow
            Route::post('/create-and-pay', [CustomerPaymentController::class, 'createAndPay'])
                ->name('createAndPay');
            Route::get('/pay', [CustomerPaymentController::class, 'showSessionPayment'])
                ->name('showSessionPayment');
            Route::post('/process', [CustomerPaymentController::class, 'processSessionPayment'])
                ->name('processSessionPayment');
            
            // Payment success page
            Route::get('/success/{paymentId}', [CustomerPaymentController::class, 'paymentSuccess'])
                ->name('success');
        });
        
        // Payment Management Routes
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/{paymentId}', [PaymentController::class, 'show'])->name('show');
            Route::post('/{paymentId}/verify', [PaymentController::class, 'verifyPayment'])->name('verifyPayment');
            Route::get('/{paymentId}/invoice', [PaymentController::class, 'generateInvoice'])->name('generateInvoice');
            Route::post('/{invoiceId}/email', [PaymentController::class, 'emailInvoice'])->name('emailInvoice');
            Route::post('/generate-report', [PaymentController::class, 'generateReport'])->name('generateReport');
            
            // API endpoints for processing payments
            Route::post('/process', [PaymentController::class, 'apiProcessPayment'])->name('apiProcess');
            Route::get('/status/{paymentId}', [PaymentController::class, 'apiGetPaymentStatus'])->name('apiStatus');
        });

        // Invoice Management
        Route::prefix('invoice')->name('invoice.')->group(function () {
            Route::post('/{invoiceId}/email', [PaymentController::class, 'emailInvoice'])->name('email');
        });
        
        // Refund Management
        Route::prefix('refunds')->name('refunds.')->group(function () {
            Route::get('/', [RefundController::class, 'index'])->name('index');
            Route::get('/{refundId}', [RefundController::class, 'show'])->name('show');
            Route::post('/{refundId}/approve', [RefundController::class, 'approve'])->name('approve');
            Route::post('/{refundId}/reject', [RefundController::class, 'reject'])->name('reject');
            Route::post('/bulk', [RefundController::class, 'bulkProcess'])->name('bulk');
            Route::post('/report', [RefundController::class, 'generateReport'])->name('report');
        });

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

        Route::get('/dashboard', [DriverDashboardController::class, 'dashboard'])->name('dashboard');

        // Package Management
        Route::get('/packages', [AssignedPackageController::class, 'assignedPackages'])->name('assignedPackages');
        Route::get('/my-packages', [DriverPackagesController::class, 'index'])->name('packages.index');
        
        // Page to view details and update status of a single package
        Route::get('/my-packages/{packageId}', [DriverPackagesController::class, 'show'])->name('packages.show');
        Route::post('/my-packages/{packageId}/update', [DriverPackagesController::class, 'updateStatus'])->name('packages.updateStatus');

        // Page to show the list of packages for status updates
        Route::get('/update-status', [DeliveryStatusController::class, 'index'])->name('status.index');
        Route::post('/update-status/{packageId}', [DeliveryStatusController::class, 'update'])->name('status.update');

        // Page for delivery history
        Route::get('/delivery-history', [DeliveryHistoryController::class, 'index'])->name('history.index');

        // Proof of Delivery routes using Factory Pattern
        Route::get('/proof/{packageId}/create', [ProofOfDeliveryController::class, 'create'])->name('proof.create');
        Route::post('/proof/{packageId}', [ProofOfDeliveryController::class, 'store'])->name('proof.store');
        Route::get('/proof/{packageId}', [ProofOfDeliveryController::class, 'show'])->name('proof.show');

        Route::get('proof/{package_id}', [ProofOfDeliveryController::class, 'create'])->name('driver.proof.create'); // Or DriverProofController if using that
                Route::post('proof/{package_id}', [ProofOfDeliveryController::class, 'store'])->name('driver.proof.store');

                Route::get('/proof/{packageId}/create', [ProofOfDeliveryController::class, 'create'])->name('proof.create');
Route::get('proof/{package_id}', [ProofOfDeliveryController::class, 'create'])->name('driver.proof.create');

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
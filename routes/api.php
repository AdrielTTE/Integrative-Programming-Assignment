<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DeliveryAssignmentController;
use App\Http\Controllers\Api\DeliveryDetailsController;
use App\Http\Controllers\Api\DeliveryDriverController;
use App\Http\Controllers\Api\LogisticHubController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ProofOfDeliveryController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\PackageController as ApiPackageController;
use App\Http\Controllers\Api\WebServiceController;
use App\Http\Controllers\AdminControllers\PaymentController;
use App\Http\Controllers\AdminControllers\RefundController;
use App\Http\Controllers\Api\NotificationController;


    Route::prefix('delivery')->group(function () {
        Route::get('/', [DeliveryController::class, 'getAll']);
        Route::post('/', [DeliveryController::class, 'add']);
        Route::get('/getBatch/{pageNo}', [DeliveryController::class, 'getBatch']);
        Route::get('/{delivery_id}', [DeliveryController::class, 'get']);
        Route::put('/{delivery_id}', [DeliveryController::class, 'update']);
        Route::delete('/{delivery_id}', [DeliveryController::class, 'delete']);
        Route::get('getCountDeliveries', [DeliveryController::class,'getCountDeliveries']);
        Route::get('/getCountByStatus/{status}', [DeliveryController::class,'getCountByStatus']);
        Route::get('/getDeliveryByPackageID/{package_id}', [DeliveryController::class,'getDeliveryByPackageID']);

    });

// -------------------
// Admin Module
// -------------------
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'getAll']);
    Route::post('/', [AdminController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [AdminController::class, 'getBatch']);
    Route::get('/{admin_id}', [AdminController::class, 'get']);
    Route::put('/{admin_id}', [AdminController::class, 'update']);
    Route::delete('/{admin_id}', [AdminController::class, 'delete']);
});

// -------------------
// Customer Module
// -------------------
Route::prefix('customer')->group(function () {
    Route::get('/', [CustomerController::class, 'getAll']);
    Route::post('/', [CustomerController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [CustomerController::class, 'getBatch']);
    Route::get('/{customer_id}', [CustomerController::class, 'get']);
    Route::put('/{customer_id}', [CustomerController::class, 'update']);
    Route::delete('/{customer_id}', [CustomerController::class, 'delete']);
    Route::get('/{customer_id}/proofs', [CustomerController::class, 'getProofs']);
    Route::get('/getCountByStatus/{status}', [CustomerController::class, 'getCountByStatus']);
});

// -------------------
// Delivery Assignment
// -------------------
Route::prefix('deliveryAssignment')->group(function () {
    Route::get('/', [DeliveryAssignmentController::class, 'getAll']);
    Route::post('/', [DeliveryAssignmentController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [DeliveryAssignmentController::class, 'getBatch']);
    Route::get('/{assignment_id}', [DeliveryAssignmentController::class, 'get']);
    Route::put('/{assignment_id}', [DeliveryAssignmentController::class, 'update']);
    Route::delete('/{assignment_id}', [DeliveryAssignmentController::class, 'delete']);
});

// -------------------
// Delivery Details
// -------------------
Route::prefix('deliveryDetails')->group(function () {
    Route::get('/', [DeliveryDetailsController::class, 'getAll']);
    Route::post('/', [DeliveryDetailsController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [DeliveryDetailsController::class, 'getBatch']);
    Route::get('/{detail_id}', [DeliveryDetailsController::class, 'get']);
    Route::put('/{detail_id}', [DeliveryDetailsController::class, 'update']);
    Route::delete('/{detail_id}', [DeliveryDetailsController::class, 'delete']);
});

// -------------------
// Delivery Driver
// -------------------
Route::prefix('deliveryDriver')->group(function () {
    Route::get('/', [DeliveryDriverController::class, 'getAll']);
    Route::post('/', [DeliveryDriverController::class, 'add']);
    Route::get('/getBatch/{pageNo}/{pageSize}/{status}', [DeliveryDriverController::class, 'getBatch']);
    Route::get('/getByStatus/{status}', [DeliveryDriverController::class, 'getCountByStatus']);
    Route::get('/{driver_id}', [DeliveryDriverController::class, 'get']);
    Route::put('/{driver_id}', [DeliveryDriverController::class, 'update']);
    Route::delete('/{driver_id}', [DeliveryDriverController::class, 'delete']);
});

// -------------------
// Feedback
// -------------------
Route::prefix('feedback')->group(function () {
    Route::get('/', [FeedbackController::class, 'getAll']);
    Route::post('/', [FeedbackController::class, 'add']);
    Route::get('/getBatch', [FeedbackController::class, 'getBatch']);
    Route::get('/getByRating/{rating}', [FeedbackController::class, 'getCountByRating']);
    Route::get('/{driver_id}', [FeedbackController::class, 'get']);
    Route::put('/{driver_id}', [FeedbackController::class, 'update']);
    Route::delete('/{driver_id}', [FeedbackController::class, 'delete']);
});

// -------------------
// Logistic Hub
// -------------------
Route::prefix('logisticHub')->group(function () {
    Route::get('/', [LogisticHubController::class, 'getAll']);
    Route::post('/', [LogisticHubController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [LogisticHubController::class, 'getBatch']);
    Route::get('/{hub_id}', [LogisticHubController::class, 'get']);
    Route::put('/{hub_id}', [LogisticHubController::class, 'update']);
    Route::delete('/{hub_id}', [LogisticHubController::class, 'delete']);
});

// -------------------
// Package (Legacy + New API v1)
// -------------------
Route::prefix('package')->group(function () {
    Route::get('/', [PackageController::class, 'getAll']);
    Route::post('/', [PackageController::class, 'add']);
    Route::get('/unassigned', [PackageController::class, 'getUnassignedPackages']);
    Route::get('/getBatch/{pageNo}', [PackageController::class, 'getBatch']);
    Route::get('/getByPackageID/{package_id}', [PackageController::class, 'get']);
    Route::put('/{package_id}', [PackageController::class, 'update']);
    Route::delete('/{package_id}', [PackageController::class, 'delete']);
    Route::get('/getCountPackage', [PackageController::class, 'getCountPackage']);
    Route::get('/getRecentPackages/{noOfRecords}', [PackageController::class, 'getRecentPackages']);
    Route::get('/getCountByStatus/{status}', [PackageController::class, 'getCountByStatus']);
    Route::get('/{package_id}/details', [PackageController::class, 'getWithDetails']);
    Route::get('/{package_id}/proof', [PackageController::class, 'getProof']);
    Route::get('/getPackagesByStatus/{status}/{page}/{pageSize}/{customerId}',[PackageController::class, 'getPackagesByStatus']);
    Route::put('/{package_id}/is-rated', [PackageController::class, 'updateIsRated']);

});

// -------------------
// Proof Of Delivery
// -------------------
Route::prefix('proofOfDelivery')->group(function () {
    Route::get('/', [ProofOfDeliveryController::class, 'getAll']);
    Route::post('/', [ProofOfDeliveryController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [ProofOfDeliveryController::class, 'getBatch']);
    Route::get('/history', [ProofOfDeliveryController::class, 'getHistory']);
    Route::post('/{proof_id}/process', [ProofOfDeliveryController::class, 'processVerification']);
    Route::post('/{proof_id}/report', [ProofOfDeliveryController::class, 'customerReport']);
    Route::get('/{proof_id}', [ProofOfDeliveryController::class, 'get']);
    Route::put('/{proof_id}', [ProofOfDeliveryController::class, 'update']);
    Route::delete('/{proof_id}', [ProofOfDeliveryController::class, 'delete']);
});

// -------------------
// Route Management
// -------------------
Route::prefix('route')->group(function () {
    Route::get('/', [RouteController::class, 'getAll']);
    Route::post('/', [RouteController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [RouteController::class, 'getBatch']);
    Route::get('/{route_id}', [RouteController::class, 'get']);
    Route::put('/{route_id}', [RouteController::class, 'update']);
    Route::delete('/{route_id}', [RouteController::class, 'delete']);
});

// -------------------
// User
// -------------------
Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'getAll']);
    Route::post('/', [UserController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [UserController::class, 'getBatch']);
    Route::get('/{user_id}', [UserController::class, 'get']);
    Route::put('/{user_id}', [UserController::class, 'update']);
    Route::delete('/{user_id}', [UserController::class, 'delete']);
});

// -------------------
// Vehicle
// -------------------
Route::prefix('vehicle')->group(function () {
    Route::get('/', [VehicleController::class, 'getAll']);
    Route::post('/', [VehicleController::class, 'add']);
    Route::get('/getBatch/{pageNo}', [VehicleController::class, 'getBatch']);
    Route::get('/{vehicle_id}', [VehicleController::class, 'get']);
    Route::put('/{vehicle_id}', [VehicleController::class, 'update']);
    Route::delete('/{vehicle_id}', [VehicleController::class, 'delete']);
    Route::get('/getCountByStatus/{status}', [VehicleController::class, 'getCountByStatus']);
});

// -------------------
// Unified API v1 (State + External + Protected)
// -------------------
Route::prefix('v1')->middleware(['api'])->group(function () {

    // Public tracking
    Route::get('packages/track/{trackingNumber}', [PackageController::class, 'track'])
        ->name('api.packages.track');

    // External integrations
    Route::prefix('external')->group(function () {
        Route::post('packages', [WebServiceController::class, 'createPackageExternal'])
            ->name('api.external.packages.create');
        Route::put('packages/{packageId}', [WebServiceController::class, 'updatePackageStatusExternal'])
            ->name('api.external.packages.update');
        Route::get('packages/{trackingNumber}', [WebServiceController::class, 'getPackageStatusExternal'])
            ->name('api.external.packages.status');
    });

    // Protected package management
    Route::middleware(['auth:sanctum'])->prefix('packages')->group(function () {
        // Basic CRUD
        Route::get('/', [PackageController::class, 'getAll'])->name('api.packages.index');
        Route::post('/', [PackageController::class, 'add'])->name('api.packages.store');
        Route::get('/{packageId}', [PackageController::class, 'get'])->name('api.packages.show');
        Route::put('/{packageId}', [PackageController::class, 'update'])->name('api.packages.update');
        Route::delete('/{packageId}', [PackageController::class, 'delete'])->name('api.packages.destroy');

        // Batch / Search
        Route::get('/batch/{pageNo}', [PackageController::class, 'getBatch'])->name('api.packages.batch');
        Route::post('/search', [PackageController::class, 'search'])->name('api.packages.search');
        Route::post('/bulk-update', [PackageController::class, 'bulkUpdate'])->name('api.packages.bulk.update');

        // State operations
        Route::post('/{packageId}/process', [PackageController::class, 'process'])->name('api.packages.process');
        Route::post('/{packageId}/cancel', [PackageController::class, 'cancel'])->name('api.packages.cancel');
        Route::post('/{packageId}/assign', [PackageController::class, 'assign'])->name('api.packages.assign');
        Route::post('/{packageId}/deliver', [PackageController::class, 'deliver'])->name('api.packages.deliver');

        // Reports & Stats
        Route::get('/statistics/{period?}', [PackageController::class, 'getStatistics'])->name('api.packages.statistics');
        Route::get('/reports/generate', [PackageController::class, 'generateReport'])->name('api.packages.reports');

        // Status / History
        Route::get('/status/{status}', [PackageController::class, 'getByStatus'])->name('api.packages.by.status');
        Route::patch('/{packageId}/status', [PackageController::class, 'updateStatus'])->name('api.packages.status.update');
        Route::get('/{packageId}/history', [PackageController::class, 'getHistory'])->name('api.packages.history');
        Route::get('/{packageId}/route', [PackageController::class, 'getRoute'])->name('api.packages.route');

        // Assignments / Alerts
        Route::get('/unassigned', [PackageController::class, 'getUnassigned'])->name('api.packages.unassigned');
        Route::get('/attention', [PackageController::class, 'getAttention'])->name('api.packages.attention');

        // Legacy support
        Route::get('/count', [PackageController::class, 'getCountPackage']);
        Route::get('/recent/{noOfRecords}', [PackageController::class, 'getRecentPackages']);
        Route::get('/count-by-status/{status}', [PackageController::class, 'getCountByStatus']);
    });
});

// -------------------
// Global Search
// -------------------
Route::get('/search/packages', [SearchController::class, 'searchPackages']);


Route::prefix('v1')->group(function () {
    // Payment API
    Route::post('/payment/process', [PaymentController::class, 'apiProcess']);
    Route::get('/payment/status/{transactionId}', [PaymentController::class, 'apiStatus']);

    // Refund API
    Route::post('/refund/request', [RefundController::class, 'apiRequest']);
    Route::get('/refund/status/{refundId}', [RefundController::class, 'apiStatus']);
});

// -------------------
// Notification
// -------------------
Route::prefix('notifications')->group(function () {
    Route::get('/nextId', [NotificationController::class, 'nextId']);
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::get('/{notification_id}', [NotificationController::class, 'show']);
    Route::put('/{notification_id}', [NotificationController::class, 'update']);
    Route::patch('/{notification_id}', [NotificationController::class, 'update']);
    Route::delete('/{notification_id}', [NotificationController::class, 'destroy']);
    Route::get('/page/{pageNo}', [NotificationController::class, 'paginated']);

});

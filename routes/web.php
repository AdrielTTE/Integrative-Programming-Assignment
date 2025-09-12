<?php

use App\Http\Controllers\AdminControllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\ProofController as WebProofController;
use App\Http\Controllers\AdminControllers\ProofManagementController;
use App\Http\Controllers\Web\SearchController as WebSearchController;
use App\Http\Controllers\AdminControllers\SearchController as AdminSearchController;

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::match(['get', 'post'], '/dashboard', [DashboardController::class, 'dashboard'])->name('adminDashboard');
    Route::get('/proofs', [ProofManagementController::class, 'index'])->name('admin.proof.index');
    Route::get('/proofs/history', [ProofManagementController::class, 'history'])->name('admin.proof.history');
    Route::get('/proofs/{proofId}', [ProofManagementController::class, 'show'])->name('admin.proof.show');
    Route::post('/proofs/{proofId}/update-status', [ProofManagementController::class, 'updateStatus'])->name('admin.proof.updateStatus');
    Route::get('/search', [AdminSearchController::class, 'search'])->name('admin.search');
    Route::post('/search/bulk', [AdminSearchController::class, 'bulkAction'])->name('admin.search.bulk');
});

Route::get('/', function () { return view('welcome'); });

// Public Routes
Route::get('/track', [PackageController::class, 'track'])->name('packages.track');
Route::post('/track', [PackageController::class, 'track'])->name('packages.track.submit');

// Customer Routes
Route::group([], function() {
    Route::get('/home', function () { return redirect()->route('customer.search'); })->name('customer.home');
    Route::get('/my-packages/search', [WebSearchController::class, 'search'])->name('customer.search');
    Route::get('/my-packages/{packageId}', [PackageController::class, 'show'])->name('customer.package.show');
    Route::post('/proofs/{proofId}/report', [WebProofController::class, 'report'])->name('customer.proof.report');
});

// General Package Routes
Route::group([], function () {
    Route::get('/dashboard', [PackageController::class, 'dashboard'])->name('packages.dashboard');
    Route::resource('packages', PackageController::class)->except(['show']);
    Route::post('/packages/bulk-update', [PackageController::class, 'bulkUpdate'])->name('packages.bulk.update');
    Route::get('/packages/reports/generate', [PackageController::class, 'generateReport'])->name('packages.reports.generate');
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\DeliveryController;

Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::apiResource('deliveries', DeliveryController::class);
});

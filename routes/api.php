<?php

use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', LoginController::class);

    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        Route::get('dashboard', DashboardController::class);

        Route::apiResource('invoices', InvoiceController::class)
            ->only(['index', 'store', 'show']);
    });
});
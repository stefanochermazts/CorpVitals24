<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\CompaniesController;
use App\Http\Controllers\Api\V1\KpisController;

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/health', HealthController::class);
    Route::get('/companies', [CompaniesController::class, 'index']);
    Route::get('/kpis', [KpisController::class, 'index']);
    Route::post('/kpis/calculate', [KpisController::class, 'calculate']);
    Route::post('/imports', [KpisController::class, 'import']);
});



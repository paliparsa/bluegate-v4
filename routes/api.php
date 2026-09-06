<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\CatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);
    Route::get('/catalog', [CatalogController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', fn (\Illuminate\Http\Request $request) => $request->user());
    });
});

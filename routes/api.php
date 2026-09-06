<?php
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\ResellerApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);
    Route::get('/catalog', [CatalogController::class, 'index']);

    Route::prefix('reseller')->middleware(['api.key','api.log','throttle:120,1'])->group(function(){
        Route::get('/catalog',[ResellerApiController::class,'catalog'])->middleware('api.ability:catalog.read');
        Route::get('/balance',[ResellerApiController::class,'balance'])->middleware('api.ability:balance.read');
        Route::get('/orders',[ResellerApiController::class,'orders'])->middleware('api.ability:orders.read');
        Route::post('/orders',[ResellerApiController::class,'createOrder'])->middleware('api.ability:orders.write');
        Route::post('/orders/{id}/retry',[ResellerApiController::class,'retry'])->middleware('api.ability:orders.write');
        Route::get('/services',[ResellerApiController::class,'services'])->middleware('api.ability:services.read');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', fn (\Illuminate\Http\Request $request) => $request->user());
    });
});

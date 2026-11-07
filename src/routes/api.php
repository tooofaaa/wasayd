<?php

use App\Http\Controllers\Api\V1\ItemApiController;
use App\Http\Controllers\Api\V1\StockApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('items', ItemApiController::class)->only(['index', 'show']);
    Route::get('stock', [StockApiController::class, 'index']);
    Route::apiResource('transactions', TransactionApiController::class)->only(['index', 'show']);
});

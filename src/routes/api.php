<?php

// Plan reference: Feature 1.1 — health check endpoint (Test T1.1-B)
// Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 6.3 (REST API v1)

use App\Http\Controllers\Api\V1\ItemApiController;
use App\Http\Controllers\Api\V1\StockApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

// ─── Health check (public, no auth) ──────────────────────────────────────────
// Plan T1.1-B: Laravel responds on port 80 and returns service statuses
Route::get('/health', function () {
    $services = [];

    // Database (PostgreSQL — ADR-002)
    try {
        DB::select('SELECT 1');
        $services['database'] = 'ok';
    } catch (\Throwable) {
        $services['database'] = 'error';
    }

    // Redis
    try {
        Redis::ping();
        $services['redis'] = 'ok';
    } catch (\Throwable) {
        $services['redis'] = 'error';
    }

    $status = collect($services)->every(fn ($s) => $s === 'ok') ? 'ok' : 'degraded';

    return response()->json([
        'status'   => $status,
        'service'  => 'wasayd-api',
        'version'  => config('app.version', '0.1.0'),
        'services' => $services,
    ], $status === 'ok' ? 200 : 503);
});

// ─── Authenticated API v1 ─────────────────────────────────────────────────────
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('items', ItemApiController::class)->only(['index', 'show']);
    Route::get('stock', [StockApiController::class, 'index']);
    Route::apiResource('transactions', TransactionApiController::class)->only(['index', 'show']);
});

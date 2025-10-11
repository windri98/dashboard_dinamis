<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DynamicApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dynamic API Routes - Protected by ApiGuardMiddleware
Route::middleware('api.guard')->group(function () {
    // Table info endpoint (untuk mengetahui struktur table dan column types)
    Route::get('/dynamic/{tableName}/info', [DynamicApiController::class, 'getTableInfo']);
    
    // Dynamic CRUD API for any table
    Route::get('/dynamic/{tableName}', [DynamicApiController::class, 'index']);
    Route::get('/dynamic/{tableName}/{id}', [DynamicApiController::class, 'show']);
    Route::post('/dynamic/{tableName}', [DynamicApiController::class, 'store']);
    Route::put('/dynamic/{tableName}/{id}', [DynamicApiController::class, 'update']);
    Route::delete('/dynamic/{tableName}/{id}', [DynamicApiController::class, 'destroy']);
});

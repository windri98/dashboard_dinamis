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
    // Dynamic CRUD API for any table
    Route::get('/dynamic/{tableName}', [DynamicApiController::class, 'index']);
    Route::get('/dynamic/{tableName}/{id}', [DynamicApiController::class, 'show']);
    Route::post('/dynamic/{tableName}', [DynamicApiController::class, 'store']);
    Route::put('/dynamic/{tableName}/{id}', [DynamicApiController::class, 'update']);
    Route::delete('/dynamic/{tableName}/{id}', [DynamicApiController::class, 'destroy']);
});

// Static API Routes (if needed)
// Route::middleware('api.guard')->group(function () {
//     Route::get('/users', [UserController::class, 'index']);
//     Route::post('/users', [UserController::class, 'store']);
//     Route::put('/users/{id}', [UserController::class, 'update']);
//     Route::delete('/users/{id}', [UserController::class, 'destroy']);
// });

// Specific endpoint with ID (if needed for special cases)
// Route::middleware('api.guard:1')->get('/users/special', [UserController::class, 'specialEndpoint']);

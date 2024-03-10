<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
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


Route::prefix('v1')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);
    });
    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Tasks routes
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::get('/deleted', [TaskController::class, 'deleted']);
            Route::get('/{id}', [TaskController::class, 'show']);
            Route::post('/', [TaskController::class, 'store']);
            Route::put('/{id}', [TaskController::class, 'update']);
            Route::delete('/{id}', [TaskController::class, 'destroy']);
        });
    });
});


// Not found route
Route::fallback(function () {
    return ApiResponse::error('Not found', 'general:not-found', 404);
});

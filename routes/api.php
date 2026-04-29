<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FlightController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// -------------------------------------------------------
// Public routes (no token needed)
// -------------------------------------------------------

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Public flight browsing (anyone can browse)
Route::get('/flights',        [FlightController::class, 'index']);
Route::get('/flights/search', [FlightController::class, 'search']);
Route::get('/flights/{id}',   [FlightController::class, 'show']);

// -------------------------------------------------------
// Protected Routes (token required)
// -------------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin: Flight management
    Route::post('/admin/flights',        [FlightController::class, 'store']);
    Route::put('/admin/flights/{id}',    [FlightController::class, 'update']);
    Route::delete('/admin/flights/{id}', [FlightController::class, 'destroy']);

});

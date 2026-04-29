<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FlightController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\PassengerController;
use App\Http\Controllers\API\SavedFlightController;



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

    // Bookings
    Route::get('/bookings',              [BookingController::class, 'index']);
    Route::post('/bookings',             [BookingController::class, 'store']);
    Route::get('/bookings/{id}',         [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Passengers
    Route::get('/bookings/{id}/passengers',              [PassengerController::class, 'index']);
    Route::post('/bookings/{id}/passengers',             [PassengerController::class, 'store']);
    Route::delete('/bookings/{id}/passengers/{pid}',     [PassengerController::class, 'destroy']);

    // Saved flights
    Route::get('/saved-flights',                [SavedFlightController::class, 'index']);
    Route::post('/flights/{id}/save',           [SavedFlightController::class, 'save']);
    Route::delete('/flights/{id}/unsave',       [SavedFlightController::class, 'unsave']);

});

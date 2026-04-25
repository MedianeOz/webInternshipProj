<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

// Public routes (no token needed)
Route::post('/register', [AuthController::class, 'register']);
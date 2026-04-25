<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        // At this point, all inputs are already validated
        // by RegisterRequest — no need to validate manually here

        // Create the user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // hashed automatically via cast in User model
            'phone'    => $request->phone,
            'role'     => 'user',
        ]);

        // Generate Sanctum token for immediate login after registration
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data'    => [
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role'  => $user->role,
                ],
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        // Check if credentials are correct
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials. Please check your email and password.',
            ], 401);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Revoke all old tokens (optional but clean — one session at a time)
        $user->tokens()->delete();

        // Generate a fresh token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role'  => $user->role,
                ],
                'token' => $token,
            ],
        ], 200);
    }

    public function logout()
    {
        // Revoke all tokens for the currently authenticated user
        Auth::user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ], 200);
    }



}
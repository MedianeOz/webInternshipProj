<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // -----------------------------------------------
    // GET /api/profile
    // Get profile of the authenticated user
    // -----------------------------------------------
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ], 200);
    }

    // -----------------------------------------------
    // PUT /api/profile
    // Change the profile
    // -----------------------------------------------
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        // Collect only the fields we allow to update
        $data = $request->only(['name', 'phone']);

        // If a new password is provided, hash it automatically via User model cast
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => $user,
        ], 200);
    }
}
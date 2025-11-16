<?php
// app/Http/Controllers/Api/ProfileController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\FarmerProfileResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get the authenticated farmer's profile.
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        // Ensure user is a farmer and has a vendor profile
        if ($user->role !== 'farmer' || !$user->vendor) {
            return response()->json(['message' => 'Farmer profile not found.'], 404);
        }

        // Return the formatted data using our resource
        return new FarmerProfileResource($user->load('vendor'));
    }

    /**
     * Update the authenticated farmer's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'farmer' || !$user->vendor) {
            return response()->json(['message' => 'Farmer profile not found.'], 404);
        }

        // 1. Validate the incoming data (using React's camelCase keys)
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone_number')->ignore($user->id)],
            'location' => 'nullable|string|max:255',

            'farmName' => 'required|string|max:255',
            'farmAddress' => 'required|string|max:255',
            'farmSize' => 'nullable|string|max:100',
            'experience' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        // 2. Use a transaction to safely update both tables
        try {
            DB::transaction(function () use ($user, $validated) {
                // Update the 'users' table
                $user->update([
                    'name' => $validated['fullName'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'location' => $validated['location'],
                ]);

                // Update the related 'vendors' table
                $user->vendor()->update([
                    'farm_name' => $validated['farmName'],
                    'farm_address' => $validated['farmAddress'],
                    'farm_size' => $validated['farmSize'],
                    'experience' => $validated['experience'],
                    'farm_description' => $validated['description'],
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Profile update failed!', 'error' => $e->getMessage()], 500);
        }

        // 3. Return the fresh, updated profile
        return new FarmerProfileResource($user->fresh()->load('vendor'));
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        // Check if the current password is correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'The provided current password does not match.'], 422);
        }

        // Update the password
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    /**
     * Update the authenticated user's profile photo.
     */
    public function updateProfilePhoto(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB Max
        ]);

        // 1. Delete the old photo if it exists to save space
        if ($user->profile_photo_url) {
            Storage::disk('public')->delete($user->profile_photo_url);
        }

        // 2. Store the new photo in 'public/profile-photos'
        $path = $request->file('photo')->store('profile-photos', 'public');

        // 3. Save the public URL to the user
        $user->update([
            'profile_photo_url' => Storage::url($path)
        ]);

        return response()->json([
            'message' => 'Photo updated successfully.',
            'profile_photo_url' => $user->profile_photo_url
        ]);
    }
}

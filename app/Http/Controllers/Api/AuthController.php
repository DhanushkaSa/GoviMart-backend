<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;       // <-- Import DB Facade
use Illuminate\Support\Facades\Validator; // <-- Import Validator
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Vendor; // <-- Import Vendor Model

class AuthController extends Controller
{
    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        // 1. Validation with conditional rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:buyer,farmer', // Must be one of these

            // Farmer-specific fields (required only if role is 'farmer')
            'farm_name' => 'required_if:role,farmer|string|max:255',
            'farm_location' => 'required_if:role,farmer|string|max:255',
            'farm_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Use a Database Transaction
        // This ensures if the vendor profile fails, the user is not created either.
        try {
            DB::beginTransaction();

            // 3. Create the User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // 4. If the role is 'farmer', create the vendor profile
            if ($request->role === 'farmer') {
                $user->vendor()->create([
                    'farm_name' => $request->farm_name,
                    'farm_location' => $request->farm_location,
                    'farm_description' => $request->farm_description,
                ]);
            }

            // 5. Commit the transaction
            DB::commit();

            // 6. Return a success response
            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user->load('vendor') // Eager load vendor data if it exists
            ], 201);
        } catch (\Exception $e) {
            // 7. Rollback on failure
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle user login (supports email or phone number).
     */
    public function login(Request $request)
    {
        // 1. Validate 'login' (can be email or phone) and 'password'
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Determine if login is email or phone
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        $credentials = [
            $fieldType => $request->login,
            'password' => $request->password
        ];

        // 3. Attempt to authenticate
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        // 4. Get the authenticated user
        $user = User::where($fieldType, $request->login)->first();



        // 5. Create and return the token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'user' => $user->load('vendor'), // Send user data (with vendor info if they are a farmer)
            'token' => $token
        ], 200);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully!'
        ], 200);
    }

    protected function redirectTo($request)
    {
        // Return null for API requests (expects JSON)
        if ($request->expectsJson()) {
            return null;
        }

        return route('login'); // default for web
    }
}

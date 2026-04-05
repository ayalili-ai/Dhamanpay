<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:100|unique:users,email',
            'role' => 'required|string|in:customer,merchant,courier,admin',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/'
            ],

            'store_name' => 'nullable|string|max:150',
            'commercial_register' => 'nullable|string|max:100',

            'wilaya' => 'nullable|string|max:100',
            'delivery_type' => 'nullable|string|max:50',

            'vehicle_matricule' => 'nullable|string|max:100',
            'delivery_company' => 'nullable|string|max:150',

            'admin_code' => 'nullable|string|max:100',

            'card_number' => 'nullable|string|max:30',
            'card_expiry' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one number, and one special character.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        if ($request->role === 'merchant') {
            $roleValidator = Validator::make($request->all(), [
                'store_name' => 'required|string|max:150',
                'commercial_register' => 'required|string|max:100',
            ]);

            if ($roleValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'error' => $roleValidator->errors()
                ], 422);
            }
        }

        if ($request->role === 'customer') {
            $roleValidator = Validator::make($request->all(), [
                'wilaya' => 'required|string|max:100',
                'delivery_type' => 'required|string|max:50',
            ]);

            if ($roleValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'error' => $roleValidator->errors()
                ], 422);
            }
        }

        if ($request->role === 'courier') {
            $roleValidator = Validator::make($request->all(), [
                'vehicle_matricule' => 'required|string|max:100',
                'delivery_company' => 'required|string|max:150',
            ]);

            if ($roleValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'error' => $roleValidator->errors()
                ], 422);
            }
        }

        if ($request->role === 'admin') {
            $roleValidator = Validator::make($request->all(), [
                'admin_code' => 'required|string|max:100',
            ]);

            if ($roleValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'error' => $roleValidator->errors()
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password),

                'store_name' => $request->role === 'merchant' ? $request->store_name : null,
                'commercial_register' => $request->role === 'merchant' ? $request->commercial_register : null,

                'wilaya' => $request->role === 'customer' ? $request->wilaya : null,
                'delivery_type' => $request->role === 'customer' ? $request->delivery_type : null,

                'vehicle_matricule' => $request->role === 'courier' ? $request->vehicle_matricule : null,
                'delivery_company' => $request->role === 'courier' ? $request->delivery_company : null,

                'admin_code' => $request->role === 'admin' ? $request->admin_code : null,

                'card_number' => $request->role === 'customer' ? $request->card_number : null,
                'card_expiry' => $request->role === 'customer' ? $request->card_expiry : null,

                'latitude' => $request->role === 'courier' ? $request->latitude : null,
                'longitude' => $request->role === 'courier' ? $request->longitude : null,

                'rating' => $request->role === 'courier' ? 0 : null,
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending' => 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $user
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error registering user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function login(Request $request)
    {    
    
        // 1. Validate input
        $validator = \Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        // 2. Find user by phone
        $user = \App\Models\User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // 3. Check password
        if (!\Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }

        // 4. Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Success
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'data' => $user
        ], 200);
    } 
    
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Current user fetched',
            'data' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }
}
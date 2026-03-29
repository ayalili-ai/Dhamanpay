<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller{
    public function register(Request $request){
        try {
            
            $request->validate([
                'full_name' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|unique:users,email',
                'password' => 'nullable|string|min:6',
                'role' => 'required|string|in:customer,courier,admin',
            ]);
            
            $user = User::create([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                //'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $user
            ], 201);
        }
            catch (ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'error' => $e->errors()
                ], 422);

            }
            catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error registering user',
                    'error' => $e->getMessage()
                ], 500);
            }
    }
}
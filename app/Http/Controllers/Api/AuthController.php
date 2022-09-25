<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private function serverError($errors)
    {
        return response()->json([
            'status' => false,
            'message' => 'Server error',
            'errors' => $errors->getMessage()
        ], 500);
    }


    public function register(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'name' => 'min:3 | max:50',
                'email' => 'email | unique:users',
                'password' => 'string | confirmed | min:5',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors(),
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $user->createToken('e-commerce-token')->plainTextToken
            ], 201);
        } catch (\Throwable $errors) {
            return $this->serverError($errors);
        }
    }


    public function login(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'email' => 'email',
                'password' => 'required | string',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors(),
                ], 401);
            }

            if (Auth::attempt($request->only(['email', 'password']))) {
                $user = User::where('email', $request->email)->first();

                return response()->json([
                    'status' => true,
                    'message' => 'User logged in successfully',
                    'user' => $user,
                    'token' => $user->createToken('e-commerce-token')->plainTextToken
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Email or password does not match with our record',
            ], 401);
        } catch (\Throwable $errors) {
            return $this->serverError($errors);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'username' => 'required|min:3|regex:/^[a-zA-Z0-9._]+$/',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = new User();
        $user->full_name = $request->full_name;
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->save();

        $response = [
            'status' => 'success',
            'message' => 'User registration successful',
            'data' => [
                'full_name' => $user->full_name,
                'username' => $user->username,
                'updated_at' => $user->updated_at,
                'created_at' => $user->created_at,
                'id' => $user->id,
                'token' => $user->createToken('auth_token')->plainTextToken,
                'role' => 'user'
            ],
        ];

        return response()->json($response, 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = Administrator::where('username', $request->username)->first();
        $guard = 'admin';

        if (!$user) {
            $user = User::where('username', $request->username)->first();
            $guard = 'web';
        }

        if (!Auth::guard($guard)->attempt($request->only('username', 'password'))) {
            return response()->json([
                "status" => "authentication_failed",
                "message" => "The username or password you entered is incorrect"
            ], 400);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $response = [
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'token' => $token,
                'role' => $guard === 'web' ? 'user' : 'admin'
            ],
        ];

        return response()->json($response, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->Tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful'
        ], 200);
    }
}

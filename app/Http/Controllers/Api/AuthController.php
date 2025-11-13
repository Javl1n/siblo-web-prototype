<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new student account.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create user account (always as student for game client)
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'student',
            ]);

            // Create player profile
            PlayerProfile::create([
                'user_id' => $user->id,
                'trainer_name' => $request->trainer_name,
                'level' => 1,
                'experience_points' => 0,
                'coins' => 0,
            ]);

            // Generate Sanctum token
            $token = $user->createToken('game-client')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Registration successful! Welcome to SIBLO.',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Login and receive Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Attempt to authenticate
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $user = Auth::user();

        // Ensure user is a student (game client should only allow students)
        if (! $user->isStudent()) {
            Auth::logout();

            return response()->json([
                'message' => 'This account is not authorized to access the game.',
            ], 403);
        }

        // Revoke existing tokens for this device
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('game-client')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Logout and revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful. See you next time!',
        ], 200);
    }
}

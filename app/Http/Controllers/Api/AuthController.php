<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SocialAuthRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'language' => $validated['language'] ?? 'en'
        ]);

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account is suspended'
            ], 403);
        }

        // Update device info if provided
        if ($request->has('fcm_token') || $request->has('device_name')) {
            $user->updateDeviceInfo([
                'fcm_token' => $request->fcm_token,
                'device_name' => $request->device_name,
                'last_login' => now()
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'mobile_app', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function socialAuth(SocialAuthRequest $request, string $provider)
    {
        $validated = $request->validated();
        $providerIdField = $provider . '_id';

        // Check if user exists with this provider ID
        $user = User::where($providerIdField, $validated['provider_id'])->first();
        
        if (!$user) {
            // Check if user exists with this email
            $user = User::where('email', $request->email)->first();
            
            if ($user) {
                // Link the social account
                $user->update([$providerIdField => $request->provider_id]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'avatar' => $request->avatar,
                    $providerIdField => $request->provider_id,
                    'email_verified_at' => now()
                ]);
            }
        }

        $token = $user->createToken('social_auth', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Social authentication successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // In a real implementation, you would send a password reset email
        // For now, we'll just return a success message
        
        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email'
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        // In a real implementation, you would verify the reset token
        // For now, we'll just update the password
        
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful'
        ]);
    }
}

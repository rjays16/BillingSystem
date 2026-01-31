<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $user = $this->authService->authenticateUser(
            $validated['email'], 
            $validated['password']
        );

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $this->authService->createTokenForUser($user);

        return response()->json([
            'user' => UserResource::make($user),
            'token' => $token,
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get current authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('organization');
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'organization' => [
                    'id' => $user->organization->id,
                    'name' => $user->organization->name,
                ],
            ],
        ]);
    }

    /**
     * Update current user's profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update user fields
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Handle password change if provided
        if (!empty($validated['current_password']) && !empty($validated['new_password'])) {
            if (!password_verify($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            if (strlen($validated['new_password']) < 8) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be at least 8 characters',
                ], 422);
            }

            $user->update([
                'password' => bcrypt($validated['new_password']),
            ]);
        }

        $user->load('organization');

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'organization' => [
                    'id' => $user->organization->id,
                    'name' => $user->organization->name,
                ],
            ],
        ]);
    }
}
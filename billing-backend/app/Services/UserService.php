<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get users for current authenticated user's organization
     */
    public function getUsersByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->userRepository->forTenant($organizationId);
    }

    /**
     * Get users for current authenticated user's organization
     */
    public function getUsersByCurrentTenant(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->userRepository->forCurrentTenant();
    }

    /**
     * Create a new user for a tenant
     */
    public function createWithOrganization(array $userData, int $organizationId)
    {
        return $this->userRepository->createWithOrganization($userData, $organizationId);
    }

    /**
     * Update user profile with password validation
     */
    public function updateUserProfile(Request $request, int $userId): JsonResponse
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['User not found'],
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        // Handle password change if provided
        if (!empty($validated['current_password']) && !empty($validated['new_password'])) {
            if (!$this->verifyPassword($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect'],
                ]);
            }

            if (strlen($validated['new_password']) < 8) {
                throw ValidationException::withMessages([
                    'new_password' => ['New password must be at least 8 characters'],
                ]);
            }

            $updateData['password'] = $this->hashPassword($validated['new_password']);
        }

        $user->update($updateData);
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

    /**
     * Update user with password hashing
     */
    public function updateUser(int $userId, array $userData, int $organizationId): ?User
    {
        // Hash password if provided
        if (isset($userData['password'])) {
            $userData['password'] = $this->hashPassword($userData['password']);
        }

        return $this->userRepository->updateForTenant($userId, $organizationId, $userData);
    }

    /**
     * Delete user from tenant
     */
    public function deleteUserFromTenant(int $userId, int $organizationId): bool
    {
        $user = $this->userRepository->findByIdForTenant($userId, $organizationId);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Find user by ID in tenant
     */
    public function findByIdForTenant(int $userId, int $organizationId): ?User
    {
        return $this->userRepository->findByIdForTenant($userId, $organizationId);
    }

    /**
     * Verify user password
     */
    private function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Hash password using bcrypt
     */
    private function hashPassword(string $password): string
    {
        return bcrypt($password);
    }
}
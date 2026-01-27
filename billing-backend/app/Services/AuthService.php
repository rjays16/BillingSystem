<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\OrganizationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private OrganizationRepositoryInterface $organizationRepository
    ) {}

    public function authenticateUser(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmailWithOrganization($email);
        
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }
        
        return $user;
    }

    public function createTokenForUser(User $user): string
    {
        // Revoke existing tokens
        $user->tokens()->delete();
        
        return $user->createToken('auth-token')->plainTextToken;
    }

    public function getUserOrganization(User $user): ?object
    {
        if (!$user->organization_id) {
            return null;
        }
        
        return $this->organizationRepository->find($user->organization_id);
    }

    public function getOrganizationUsers(int $organizationId): Collection
    {
        return $this->userRepository->getOrganizationUsers($organizationId);
    }

    public function createUserForOrganization(array $userData, string $organizationName): User
    {
        $organization = $this->organizationRepository->findByName($organizationName);
        
        if (!$organization) {
            throw new \Exception("Organization '{$organizationName}' not found");
        }
        
        return $this->userRepository->createWithOrganization($userData, $organization->id);
    }
}
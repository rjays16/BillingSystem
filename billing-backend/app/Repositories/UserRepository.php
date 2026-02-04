<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    protected function getModel(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByEmailWithOrganization(string $email): ?User
    {
        return User::with('organization')->where('email', $email)->first();
    }

    public function getOrganizationUsers(int $organizationId): Collection
    {
        return User::where('organization_id', $organizationId)->get();
    }

    public function createWithOrganization(array $data, int $organizationId): User
    {
        $userData = array_merge($data, ['organization_id' => $organizationId]);
        return User::create($userData);
    }

    public function forTenant(int $organizationId): Collection
    {
        return User::where('organization_id', $organizationId)->get();
    }

    /**
     * Get users for current authenticated user's organization
     */
    public function forCurrentTenant(): Collection
    {
        return User::all();
    }

    public function findByIdForTenant(int $id, int $organizationId): ?User
    {
        return User::where('organization_id', $organizationId)->find($id);
    }

    public function updateForTenant(int $id, int $organizationId, array $data): ?User
    {
        $user = User::where('organization_id', $organizationId)->find($id);
        
        if (!$user) {
            return null;
        }

        $user->update($data);
        return $user->fresh();
    }

    public function deleteForTenant(int $id, int $organizationId): bool
    {
        $user = User::where('organization_id', $organizationId)->find($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}
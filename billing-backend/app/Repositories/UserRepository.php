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
}
<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByEmailWithOrganization(string $email): ?User;
    public function getOrganizationUsers(int $organizationId): Collection;
    public function createWithOrganization(array $data, int $organizationId): User;
}
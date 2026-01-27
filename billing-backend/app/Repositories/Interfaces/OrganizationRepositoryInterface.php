<?php

namespace App\Repositories\Interfaces;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
    public function findByIdWithUsers(int $id): ?Organization;
    public function findByName(string $name): ?Organization;
    public function getTenantUsers(int $organizationId): Collection;
}
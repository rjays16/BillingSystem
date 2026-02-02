<?php

namespace App\Repositories;

use App\Models\Organization;
use App\Repositories\Interfaces\OrganizationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    protected function getModel(): string
    {
        return Organization::class;
    }

    public function findByIdWithUsers(int $id): ?Organization
    {
        return Organization::with('users')->find($id);
    }

    public function findByName(string $name): ?Organization
    {
        return Organization::where('name', $name)->first();
    }

    public function findById(int $id): ?Organization
    {
        return Organization::find($id);
    }

    public function getTenantUsers(int $organizationId): Collection
    {
        return Organization::find($organizationId)?->users ?? collect();
    }
}
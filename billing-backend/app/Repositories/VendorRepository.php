<?php

namespace App\Repositories;

use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VendorRepository implements VendorRepositoryInterface
{
    protected Vendor $model;

    public function __construct(Vendor $vendor)
    {
        $this->model = $vendor;
    }

    public function all(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    public function findById(int $id): ?Vendor
    {
        return $this->model->find($id);
    }

    public function create(array $data): Vendor
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Vendor
    {
        $vendor = $this->model->find($id);
        
        if (!$vendor) {
            return null;
        }

        $vendor->update($data);
        return $vendor->fresh();
    }

    public function delete(int $id): bool
    {
        $vendor = $this->model->find($id);
        
        if (!$vendor) {
            return false;
        }

        return $vendor->delete();
    }

    public function forTenant(int $organizationId): Collection
    {
        return $this->model->where('organization_id', $organizationId)->get();
    }

    /**
     * Get vendors for current authenticated user's organization
     */
    public function forCurrentTenant(): Collection
    {
        return $this->model->all();
    }

    public function findByIdForTenant(int $id, int $organizationId): ?Vendor
    {
        return $this->model->where('organization_id', $organizationId)->find($id);
    }

    public function updateForTenant(int $id, int $organizationId, array $data): ?Vendor
    {
        $vendor = $this->model->where('organization_id', $organizationId)->find($id);
        
        if (!$vendor) {
            return null;
        }

        $vendor->update($data);
        return $vendor->fresh();
    }

    public function deleteForTenant(int $id, int $organizationId): bool
    {
        $vendor = $this->model->where('organization_id', $organizationId)->find($id);
        
        if (!$vendor) {
            return false;
        }

        return $vendor->delete();
    }

    }
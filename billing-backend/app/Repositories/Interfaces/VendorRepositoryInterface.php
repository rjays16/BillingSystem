<?php

namespace App\Repositories\Interfaces;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

interface VendorRepositoryInterface
{
    public function all(): Collection;
    
    public function findById(int $id): ?Vendor;
    
    public function create(array $data): Vendor;
    
    public function update(int $id, array $data): ?Vendor;
    
    public function delete(int $id): bool;
    
    public function forTenant(int $organizationId): Collection;
    
    public function findByIdForTenant(int $id, int $organizationId): ?Vendor;
    
    public function updateForTenant(int $id, int $organizationId, array $data): ?Vendor;
    
    public function deleteForTenant(int $id, int $organizationId): bool;
}
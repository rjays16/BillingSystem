<?php

namespace App\Repositories;

use App\Models\Organization;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrganizationRepositoryInterface
{
    public function all(): Collection;
    
    public function findById(int $id): ?Organization;
    
    public function create(array $data): Organization;
    
    public function update(int $id, array $data): ?Organization;
    
    public function delete(int $id): bool;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
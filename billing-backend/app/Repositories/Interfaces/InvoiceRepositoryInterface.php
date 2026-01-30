<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function findByTenant(int $organizationId, int $perPage = 15): LengthAwarePaginator;
    public function findByTenantAndStatus(int $organizationId, string $status): Collection;
    public function findForTenant(int $id, int $organizationId): ?object;
    public function findByNumber(string $number): ?object;
    public function createForTenant(array $data, int $organizationId): object;
    public function updateForTenant(int $id, array $data, int $organizationId): object;
    public function deleteForTenant(int $id, int $organizationId): bool;
}
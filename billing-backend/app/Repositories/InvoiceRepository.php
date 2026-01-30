<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    protected function getModel(): string
    {
        return Invoice::class;
    }

    public function findByTenant(int $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->forTenant($organizationId)
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByTenantAndStatus(int $organizationId, string $status): Collection
    {
        return $this->model->forTenant($organizationId)
            ->byStatus($status)
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findForTenant(int $id, int $organizationId): ?object
    {
        return $this->model->forTenant($organizationId)->with('vendor')->find($id);
    }

    public function findByNumber(string $number): ?object
    {
        return $this->model->with('vendor')->where('number', $number)->first();
    }

    public function createForTenant(array $data, int $organizationId): object
    {
        $invoiceData = array_merge($data, ['organization_id' => $organizationId]);
        return $this->model->create($invoiceData);
    }

    public function updateForTenant(int $id, array $data, int $organizationId): object
    {
        $invoice = $this->model->forTenant($organizationId)->findOrFail($id);
        $invoice->update($data);
        return $invoice;
    }

    public function deleteForTenant(int $id, int $organizationId): bool
    {
        $invoice = $this->model->forTenant($organizationId)->findOrFail($id);
        return $invoice->delete();
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    /**
     * Display a listing of invoices for the current tenant.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = $this->getUserOrganizationId();
        $perPage = $request->get('per_page', 15);
        
        $invoices = $this->invoiceRepository->findByTenant($organizationId, $perPage);
        
        return InvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created invoice for the current tenant.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $organizationId = $this->getUserOrganizationId();
        
        $invoice = $this->invoiceRepository->createForTenant(
            $request->validated(),
            $organizationId
        );
        
        return response()->json([
            'message' => 'Invoice created successfully',
            'data' => InvoiceResource::make($invoice)
        ], 201);
    }

    /**
     * Display the specified invoice for the current tenant.
     */
    public function show(string $id): JsonResponse
    {
        $organizationId = $this->getUserOrganizationId();
        
        $invoice = $this->invoiceRepository->findForTenant($id, $organizationId);
        
        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }
        
        return response()->json([
            'data' => InvoiceResource::make($invoice)
        ]);
    }

    /**
     * Update the specified invoice for the current tenant.
     */
    public function update(UpdateInvoiceRequest $request, string $id): JsonResponse
    {
        $organizationId = $this->getUserOrganizationId();
        
        try {
            $invoice = $this->invoiceRepository->updateForTenant(
                $id,
                $request->validated(),
                $organizationId
            );
            
            return response()->json([
                'message' => 'Invoice updated successfully',
                'data' => InvoiceResource::make($invoice)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Remove the specified invoice for the current tenant.
     */
    public function destroy(string $id): JsonResponse
    {
        $organizationId = $this->getUserOrganizationId();
        
        try {
            $this->invoiceRepository->deleteForTenant($id, $organizationId);
            
            return response()->json([
                'message' => 'Invoice deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Filter invoices by status for the current tenant.
     */
    public function byStatus(Request $request, string $status): AnonymousResourceCollection
    {
        $organizationId = $this->getUserOrganizationId();
        
        $invoices = $this->invoiceRepository->findByTenantAndStatus($organizationId, $status);
        
        return InvoiceResource::collection($invoices);
    }

    /**
     * Get the current user's organization ID.
     */
    private function getUserOrganizationId(): int
    {
        return request()->user()->organization_id;
    }
}

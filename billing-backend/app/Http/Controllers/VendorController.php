<?php

namespace App\Http\Controllers;

use App\Http\Resources\VendorResource;
use App\Http\Requests\VendorStoreRequest;
use App\Http\Requests\VendorUpdateRequest;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    protected $vendorRepository;

    public function __construct(VendorRepositoryInterface $vendorRepository)
    {
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * Display a listing of the vendors.
     */
    public function index(Request $request): JsonResponse
    {
        $vendors = $this->vendorRepository->forTenant($request->user()->organization_id);
        
        return response()->json([
            'success' => true,
            'data' => VendorResource::collection($vendors)
        ]);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(VendorStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;

        $vendor = $this->vendorRepository->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vendor created successfully',
            'data' => new VendorResource($vendor)
        ], 201);
    }

    /**
     * Display the specified vendor.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendorRepository->findByIdForTenant($id, $request->user()->organization_id);
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new VendorResource($vendor)
        ]);
    }

    /**
     * Update the specified vendor.
     */
    public function update(VendorUpdateRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        
        $vendor = $this->vendorRepository->updateForTenant($id, $request->user()->organization_id, $validated);
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully',
            'data' => new VendorResource($vendor)
        ]);
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->vendorRepository->deleteForTenant($id, $request->user()->organization_id);
        
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted successfully'
        ]);
    }
}
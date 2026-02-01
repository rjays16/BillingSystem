<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrganizationResource;
use App\Repositories\Interfaces\OrganizationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrganizationController extends Controller
{
    protected $organizationRepository;

    public function __construct(OrganizationRepositoryInterface $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * Display a listing of the organizations.
     */
    public function index(Request $request): JsonResponse
    {
        $organizations = $this->organizationRepository->all();
        
        return response()->json([
            'success' => true,
            'data' => OrganizationResource::collection($organizations)
        ]);
    }

    /**
     * Store a newly created organization.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:organizations',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:3',
            'payment_terms' => 'nullable|integer|min:1|max:365',
        ]);

        $organization = $this->organizationRepository->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Organization created successfully',
            'data' => new OrganizationResource($organization)
        ], 201);
    }

    /**
     * Display the specified organization.
     */
    public function show(int $id): JsonResponse
    {
        $organization = $this->organizationRepository->findById($id);
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrganizationResource($organization)
        ]);
    }

    /**
     * Update the specified organization.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:10|unique:organizations,code,' . $id,
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:3',
            'payment_terms' => 'nullable|integer|min:1|max:365',
        ]);

        \Log::info('Updating organization', [
            'id' => $id,
            'validated_data' => $validated
        ]);

        $organization = $this->organizationRepository->update($id, $validated);
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        \Log::info('Organization updated successfully', [
            'organization' => $organization->toArray()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Organization updated successfully',
            'data' => new OrganizationResource($organization)
        ]);
    }

    /**
     * Remove the specified organization.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->organizationRepository->delete($id);
        
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Organization deleted successfully'
        ]);
    }
}
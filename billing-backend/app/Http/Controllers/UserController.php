<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\UserService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private TenantService $tenantService
    ) {}

    /**
     * Display a listing of users for the current organization.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getUsersByCurrentTenant();
        
        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users)
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $user = $this->userService->createWithOrganization(
            $validated,
            $request->user()->organization_id
        );
        
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findByIdForTenant($id, $request->user()->organization_id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        
        $user = $this->userService->updateUser($id, $validated, $request->user()->organization_id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->userService->deleteUserFromTenant($id, $request->user()->organization_id);
        
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
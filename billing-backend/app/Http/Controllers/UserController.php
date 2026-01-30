<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of users for the current organization.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userRepository->forTenant($request->user()->organization_id);
        
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
        
        $user = $this->userRepository->createForTenant(
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
        $user = $this->userRepository->findByIdForTenant($id, $request->user()->organization_id);
        
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
        
        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user = $this->userRepository->updateForTenant($id, $request->user()->organization_id, $validated);
        
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
        $deleted = $this->userRepository->deleteForTenant($id, $request->user()->organization_id);
        
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
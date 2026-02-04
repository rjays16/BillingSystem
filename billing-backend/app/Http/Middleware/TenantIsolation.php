<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    /**
     * Get the current authenticated user's organization ID
     */
    public static function getCurrentOrganizationId(): ?int
    {
        $user = request()->user();
        
        if (!$user) {
            return null;
        }
        
        return $user->organization_id;
    }

    /**
     * Check if user belongs to specified organization
     */
    public static function userBelongsToOrganization(int $organizationId): bool
    {
        $userOrganizationId = static::getCurrentOrganizationId();
        
        if (!$userOrganizationId) {
            return false;
        }
        
        return $userOrganizationId === $organizationId;
    }

    /**
     * Apply tenant scope to a model query
     */
    public static function applyTenantScope($query, ?int $organizationId)
    {
        if ($organizationId) {
            return $query->where('organization_id', $organizationId);
        }
        
        return $query;
    }

    /**
     * Validate tenant access to a resource
     */
    public static function validateTenantAccess($resource): bool
    {
        $userOrganizationId = static::getCurrentOrganizationId();
        $resourceOrganizationId = null;
        
        if ($resource instanceof User) {
            $resourceOrganizationId = $resource->organization_id;
        } elseif ($resource instanceof Vendor) {
            $resourceOrganizationId = $resource->organization_id;
        } elseif ($resource instanceof Invoice) {
            $resourceOrganizationId = $resource->organization_id;
        } elseif ($resource instanceof Organization) {
            $resourceOrganizationId = $resource->id;
        }
        
        return $userOrganizationId && $resourceOrganizationId === $userOrganizationId;
    }
}

class TenantIsolation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authenticated user
        $user = $request->user();
        
        // Skip tenant check for super admin (can access all data)
        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }

        $userOrganizationId = $user?->organization_id;

        if ($userOrganizationId) {
            $request->merge([
                'tenant_organization_id' => $userOrganizationId,
            ]);
        }

        $response = $next($request);
        
        if ($userOrganizationId) {
            $response->headers->set('X-Tenant-Organization-Id', $userOrganizationId);
        }

        return $response;
    }

    /**
     * Handle tasks after response is sent
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($request->user() && $request->user()->organization_id) {
            Log::info('Tenant access', [
                'user_id' => $request->user()->id,
                'organization_id' => $request->user()->organization_id,
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }
    }
}
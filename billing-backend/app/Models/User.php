<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'organization_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Scope to query users for a specific tenant (organization)
     */
    public function scopeForTenant($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Boot the model to automatically apply tenant scope
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function ($builder) {
            if (Auth::check()) {
                return $builder->where('organization_id', Auth::user()->organization_id);
            }
            return $builder;
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is accountant
     */
    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    /**
     * Get all valid roles
     */
    public static function getValidRoles(): array
    {
        return ['super_admin', 'admin', 'accountant'];
    }

    /**
     * Check if role is valid
     */
    public static function isValidRole(string $role): bool
    {
        return in_array($role, self::getValidRoles());
    }
}

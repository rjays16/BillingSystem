<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'organization_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to query vendors for a specific tenant (organization)
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

    /**
     * Relationship to organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Relationship to invoices
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
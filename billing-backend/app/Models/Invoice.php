<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'vendor_id',
        'amount',
        'status',
        'organization_id',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to query invoices for a specific tenant (organization)
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
     * Scope to query invoices by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Relationship to vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship to organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory, HasUlids;

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
        return $this->hasMany(\App\Models\Invoice::class);
    }
}
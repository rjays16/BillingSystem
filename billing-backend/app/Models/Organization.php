<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
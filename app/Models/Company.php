<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'business_type',
        'pan',
        'tan',
        'gstin',
        'cin',
        'address',
        'state',
        'city',
        'pincode',
        'email',
        'phone',
        'website',
        'business_description',
        'status',
    ];

    public function financialYears(): HasMany
    {
        return $this->hasMany(FinancialYear::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function activeFinancialYear(): ?FinancialYear
    {
        return $this->financialYears()->where('is_active', true)->first();
    }
}

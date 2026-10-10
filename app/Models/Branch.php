<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'primary_company_id',
        'name',
        'code',
        'address',
        'state',
        'status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeHead(Builder $query): Builder
    {
        return $query->whereNotNull('primary_company_id');
    }
}

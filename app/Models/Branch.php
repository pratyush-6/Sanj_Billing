<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * Derived from primary_company_id rather than its own stored column — a MySQL/
     * MariaDB generated-column limitation on some hosts makes a physical is_primary
     * column unreliable to add via migration, and primary_company_id is already the
     * single source of truth (its unique index is what guarantees one head per
     * company), so this is never out of sync.
     */
    protected function isPrimary(): Attribute
    {
        return Attribute::make(get: fn () => $this->primary_company_id !== null);
    }

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

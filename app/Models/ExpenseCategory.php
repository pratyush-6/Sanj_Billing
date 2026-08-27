<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'account_id',
        'name',
        'code',
        'description',
        'expense_nature',
        'tax_applicable',
        'status',
    ];

    protected $casts = [
        'tax_applicable' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(ExpenseSubCategory::class);
    }
}

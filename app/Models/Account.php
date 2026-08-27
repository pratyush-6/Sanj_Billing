<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'parent_account_id',
        'code',
        'name',
        'type',
        'key',
        'is_system',
        'status',
        'opening_balance',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    public function journalEntryItems(): HasMany
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public function isDebitNormal(): bool
    {
        return in_array($this->type, ['Asset', 'Expense'], true);
    }
}

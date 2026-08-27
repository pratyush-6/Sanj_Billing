<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'entry_date',
        'entry_number',
        'narration',
        'source_type',
        'source_id',
        'status',
        'reverses_journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function reverses(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reverses_journal_entry_id');
    }

    public function reversedBy(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reverses_journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

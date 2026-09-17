<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'goods_receipt_id',
        'party_id',
        'bill_number',
        'bill_date',
        'due_date',
        'status',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'tds_section_id',
        'tds_amount',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'taxable_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function tdsSection(): BelongsTo
    {
        return $this->belongsTo(TdsSection::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseBillItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'source');
    }

    public function journalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'source');
    }

    public function amountPaid(): float
    {
        return (float) $this->payments->where('status', 'Posted')->sum('amount');
    }

    /**
     * total_amount includes the TDS portion, but TDS is withheld (credited to
     * TDS Payable, remitted to the government separately), not paid to the
     * vendor via this bill's Payments — so the payable-in-cash amount excludes
     * it. Without this, a Payment could be recorded up to the full total_amount,
     * overdrawing what Accounts Payable was actually credited (total - tds).
     */
    public function amountDue(): float
    {
        return round((float) $this->total_amount - (float) $this->tds_amount - $this->amountPaid(), 2);
    }
}

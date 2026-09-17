<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SaleInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'party_id',
        'invoice_number',
        'invoice_date',
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
        'invoice_date' => 'date',
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
        return $this->hasMany(SaleInvoiceItem::class);
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
     * TDS is deducted by the customer at the point of payment (they remit it to
     * the government on the business's behalf), so the business never actually
     * collects that portion via a Payment — mirrors PurchaseBill::amountDue().
     */
    public function amountDue(): float
    {
        return round((float) $this->total_amount - (float) $this->tds_amount - $this->amountPaid(), 2);
    }
}

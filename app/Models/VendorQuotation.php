<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'vendor_id',
        'quotation_number',
        'quotation_date',
        'validity_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'validity_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(QuotationApproval::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'quotation_id');
    }

    public function totalAmount(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->amount);
    }

    public function isExpired(): bool
    {
        return $this->validity_date !== null
            && $this->validity_date->isPast()
            && in_array($this->status, ['Draft', 'Submitted'], true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DeliveryChallan extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'sale_order_id',
        'party_id',
        'challan_number',
        'challan_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'challan_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function saleOrder(): BelongsTo
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryChallanItem::class);
    }

    public function saleInvoiceItems(): HasManyThrough
    {
        return $this->hasManyThrough(SaleInvoiceItem::class, DeliveryChallanItem::class);
    }
}

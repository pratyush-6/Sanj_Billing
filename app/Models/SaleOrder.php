<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'party_id',
        'order_number',
        'order_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function deliveryChallans(): HasMany
    {
        return $this->hasMany(DeliveryChallan::class);
    }

    public function totalAmount(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->amount);
    }
}

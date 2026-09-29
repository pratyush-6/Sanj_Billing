<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_category_id',
        'unit_id',
        'secondary_unit_id',
        'conversion_factor',
        'sku',
        'hsn_code',
        'gst_rate_id',
        'name',
        'description',
        'min_stock_level',
        'max_stock_level',
        'status',
    ];

    protected $casts = [
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
        'conversion_factor' => 'decimal:4',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function secondaryUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'secondary_unit_id');
    }

    /**
     * Quantity is always stored/entered in the primary unit everywhere in the
     * app (purchase/sale/stock/ledger) — this is a display-only conversion
     * hint, not a second unit of storage.
     */
    public function secondaryQuantityFor(float $primaryQty): ?float
    {
        if (! $this->secondary_unit_id || ! $this->conversion_factor) {
            return null;
        }

        return round($primaryQty * (float) $this->conversion_factor, 4);
    }

    public function gstRate(): BelongsTo
    {
        return $this->belongsTo(GstRate::class);
    }

    public function displayLabel(): string
    {
        return $this->sku ? "{$this->name} ({$this->sku})" : $this->name;
    }

    public function vendorProducts(): HasMany
    {
        return $this->hasMany(VendorProduct::class);
    }
}

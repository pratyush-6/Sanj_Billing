<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'product_id',
        'vendor_sku',
        'price',
        'lead_time_days',
        'is_preferred',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_preferred' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'vendor_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

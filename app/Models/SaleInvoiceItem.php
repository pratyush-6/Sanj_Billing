<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_invoice_id',
        'delivery_challan_item_id',
        'product_id',
        'hsn_code',
        'gst_rate',
        'quantity',
        'unit_price',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'amount',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'gst_rate' => 'decimal:2',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function saleInvoice(): BelongsTo
    {
        return $this->belongsTo(SaleInvoice::class);
    }

    public function deliveryChallanItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryChallanItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

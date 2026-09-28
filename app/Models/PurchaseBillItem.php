<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_bill_id',
        'goods_receipt_item_id',
        'purchase_order_item_id',
        'hsn_code',
        'gst_rate',
        'quantity',
        'unit_price',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'amount',
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
    ];

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * A new-style line goes straight to purchaseOrderItem; a historical line
     * (created before Goods Receipt was folded into Purchase Order) only has
     * goodsReceiptItem, one hop further to the same PurchaseOrderItem/product.
     */
    public function product(): ?Product
    {
        return $this->purchaseOrderItem?->product ?? $this->goodsReceiptItem?->purchaseOrderItem?->product;
    }
}

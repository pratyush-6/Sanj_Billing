<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;

/**
 * Goods Receipt is retired as an active workflow — a Purchase Order now posts
 * stock and enables billing directly on submit (see PurchaseOrderService).
 * Only this read-only view survives, so receipts created before that change
 * (and the Purchase Bills created from them) keep resolving correctly.
 */
class GoodsReceiptController extends Controller
{
    use EnsuresCompanyOwnership;

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->ensureBelongsToCurrentCompany($goodsReceipt);

        $goodsReceipt->load(['purchaseOrder.vendor', 'creator', 'items.purchaseOrderItem.product', 'purchaseBill']);

        return view('goods-receipts.show', ['goodsReceipt' => $goodsReceipt]);
    }
}

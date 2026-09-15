<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GoodsReceiptService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private StockMovementService $stockMovementService,
    ) {}

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): GoodsReceipt
    {
        return DB::transaction(function () use ($data, $company, $financialYear, $creator) {
            $items = $data['items'];
            unset($data['items']);

            $purchaseOrder = PurchaseOrder::where('company_id', $company->id)
                ->whereIn('status', ['Sent', 'Partially Received'])
                ->with('items.product')
                ->findOrFail($data['purchase_order_id']);

            $goodsReceipt = GoodsReceipt::create([
                ...$data,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'grn_number' => $this->generateSequentialNumber(GoodsReceipt::class, 'GRN', $company, $financialYear),
                'status' => 'Draft',
                'created_by' => $creator->id,
            ]);

            $this->syncItems($goodsReceipt, $purchaseOrder, $items);

            $this->auditLog->log('Goods Receipt Created', 'Goods Receipt', $goodsReceipt, null, $goodsReceipt->toArray());

            return $goodsReceipt;
        });
    }

    public function update(GoodsReceipt $goodsReceipt, array $data): GoodsReceipt
    {
        if ($goodsReceipt->status !== 'Draft') {
            throw new RuntimeException('Only draft goods receipts can be edited.');
        }

        return DB::transaction(function () use ($goodsReceipt, $data) {
            $old = $goodsReceipt->toArray();
            $items = $data['items'];
            unset($data['items'], $data['purchase_order_id']);

            $goodsReceipt->update($data);
            $goodsReceipt->purchaseOrder->load('items.product');
            $this->syncItems($goodsReceipt, $goodsReceipt->purchaseOrder, $items);

            $this->auditLog->log('Goods Receipt Updated', 'Goods Receipt', $goodsReceipt, $old, $goodsReceipt->toArray());

            return $goodsReceipt;
        });
    }

    public function complete(GoodsReceipt $goodsReceipt, User $actor): GoodsReceipt
    {
        if ($goodsReceipt->status !== 'Draft') {
            throw new RuntimeException('Only draft goods receipts can be completed.');
        }

        if ($goodsReceipt->items()->count() === 0) {
            throw new RuntimeException('Add at least one received line item before completing.');
        }

        return DB::transaction(function () use ($goodsReceipt, $actor) {
            $goodsReceipt->load(['items.purchaseOrderItem.product', 'company', 'financialYear', 'purchaseOrder.items']);

            foreach ($goodsReceipt->items as $item) {
                $this->stockMovementService->postIn(
                    $goodsReceipt->company,
                    $goodsReceipt->financialYear,
                    $item->purchaseOrderItem->product,
                    (float) $item->quantity_received,
                    $goodsReceipt->receipt_date->toDateString(),
                    $goodsReceipt,
                    $goodsReceipt->grn_number,
                    null,
                    $actor,
                );
            }

            $goodsReceipt->update(['status' => 'Completed']);

            $this->refreshPurchaseOrderStatus($goodsReceipt->purchaseOrder);

            $this->auditLog->log('Goods Receipt Completed', 'Goods Receipt', $goodsReceipt, null, ['status' => 'Completed']);

            return $goodsReceipt;
        });
    }

    private function refreshPurchaseOrderStatus(PurchaseOrder $purchaseOrder): void
    {
        $receivedByItem = GoodsReceiptItem::whereIn('purchase_order_item_id', $purchaseOrder->items->pluck('id'))
            ->whereHas('goodsReceipt', fn ($query) => $query->where('status', 'Completed'))
            ->selectRaw('purchase_order_item_id, SUM(quantity_received) as total_received')
            ->groupBy('purchase_order_item_id')
            ->pluck('total_received', 'purchase_order_item_id');

        $fullyReceived = true;
        $anyReceived = false;

        foreach ($purchaseOrder->items as $item) {
            $received = (float) ($receivedByItem[$item->id] ?? 0);

            if ($received > 0) {
                $anyReceived = true;
            }

            if ($received < (float) $item->quantity) {
                $fullyReceived = false;
            }
        }

        $status = $fullyReceived ? 'Received' : ($anyReceived ? 'Partially Received' : $purchaseOrder->status);

        if ($status !== $purchaseOrder->status) {
            $purchaseOrder->update(['status' => $status]);
        }
    }

    /**
     * Guards against over-receiving by comparing against quantity already received
     * across every *completed* GRN for this PO item — never a stored running total,
     * so a stale form submitted after another receipt is still checked against
     * current reality inside this same transaction.
     */
    private function syncItems(GoodsReceipt $goodsReceipt, PurchaseOrder $purchaseOrder, array $items): void
    {
        $goodsReceipt->items()->delete();

        $receivedByItem = GoodsReceiptItem::whereIn('purchase_order_item_id', $purchaseOrder->items->pluck('id'))
            ->whereHas('goodsReceipt', fn ($query) => $query->where('status', 'Completed'))
            ->selectRaw('purchase_order_item_id, SUM(quantity_received) as total_received')
            ->groupBy('purchase_order_item_id')
            ->pluck('total_received', 'purchase_order_item_id');

        $poItemsById = $purchaseOrder->items->keyBy('id');

        foreach ($items as $item) {
            $poItem = $poItemsById->get((int) $item['purchase_order_item_id']);

            if (! $poItem) {
                throw new RuntimeException('One of the selected items does not belong to this purchase order.');
            }

            $alreadyReceived = (float) ($receivedByItem[$poItem->id] ?? 0);
            $remaining = round((float) $poItem->quantity - $alreadyReceived, 2);
            $quantityReceived = round((float) $item['quantity_received'], 2);

            if ($quantityReceived > $remaining + 0.01) {
                throw new RuntimeException("Cannot receive more than the remaining quantity for {$poItem->product->name} ({$remaining} left).");
            }

            $goodsReceipt->items()->create([
                'purchase_order_item_id' => $poItem->id,
                'quantity_received' => $quantityReceived,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}

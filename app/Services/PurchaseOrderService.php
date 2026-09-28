<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private StockMovementService $stockMovementService,
    ) {}

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $company, $financialYear, $creator) {
            $items = $data['items'];
            unset($data['items']);

            $purchaseOrder = PurchaseOrder::create([
                ...$data,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'po_number' => $this->generateSequentialNumber(PurchaseOrder::class, 'PO', $company, $financialYear),
                'status' => 'Draft',
                'created_by' => $creator->id,
            ]);

            $this->syncItems($purchaseOrder, $items);

            $this->auditLog->log('Purchase Order Created', 'Purchase Order', $purchaseOrder, null, $purchaseOrder->toArray());

            return $purchaseOrder;
        });
    }

    public function update(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'Draft') {
            throw new RuntimeException('Only draft purchase orders can be edited.');
        }

        return DB::transaction(function () use ($purchaseOrder, $data) {
            $old = $purchaseOrder->toArray();
            $items = $data['items'];
            unset($data['items']);

            $purchaseOrder->update($data);
            $this->syncItems($purchaseOrder, $items);

            $this->auditLog->log('Purchase Order Updated', 'Purchase Order', $purchaseOrder, $old, $purchaseOrder->toArray());

            return $purchaseOrder;
        });
    }

    /**
     * The single point of no return: submitting a PO is both "sent to the
     * vendor" and "goods received" in this simplified workflow — it posts a
     * stock-In movement per line at the PO's own price (the same call shape
     * GoodsReceiptService::complete() used to make, just sourced to the PO
     * itself) and immediately makes the PO eligible for billing. A mistake
     * discovered afterwards is a Stock Adjustment, not a cancellation — the
     * same rule every other "stock has already moved" document in this app
     * follows (a Completed Delivery Challan, a Posted Sale Invoice's direct
     * line).
     */
    public function submit(PurchaseOrder $purchaseOrder, User $actor): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'Draft') {
            throw new RuntimeException('Only draft purchase orders can be submitted.');
        }

        if ($purchaseOrder->items()->count() === 0) {
            throw new RuntimeException('Add at least one line item before submitting.');
        }

        return DB::transaction(function () use ($purchaseOrder, $actor) {
            $purchaseOrder->load(['items.product', 'company', 'financialYear']);

            foreach ($purchaseOrder->items as $item) {
                $this->stockMovementService->postIn(
                    $purchaseOrder->company,
                    $purchaseOrder->financialYear,
                    $item->product,
                    (float) $item->quantity,
                    $purchaseOrder->po_date->toDateString(),
                    $purchaseOrder,
                    $purchaseOrder->po_number,
                    null,
                    $actor,
                    (float) $item->unit_price,
                );
            }

            $purchaseOrder->update(['status' => 'Submitted']);

            $this->auditLog->log('Purchase Order Submitted', 'Purchase Order', $purchaseOrder, null, ['status' => 'Submitted']);

            return $purchaseOrder;
        });
    }

    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'Draft') {
            throw new RuntimeException('Only a draft purchase order can be cancelled.');
        }

        $purchaseOrder->update(['status' => 'Cancelled']);
        $this->auditLog->log('Purchase Order Cancelled', 'Purchase Order', $purchaseOrder, null, ['status' => 'Cancelled']);

        return $purchaseOrder;
    }

    private function syncItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        $purchaseOrder->items()->delete();

        foreach ($items as $item) {
            $quantity = round((float) $item['quantity'], 2);
            $unitPrice = round((float) $item['unit_price'], 2);

            $purchaseOrder->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => round($quantity * $unitPrice, 2),
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\VendorQuotation;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderService
{
    use GeneratesSequentialNumbers;

    public function __construct(private AuditLogService $auditLog) {}

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $company, $financialYear, $creator) {
            $items = $data['items'];
            unset($data['items']);

            $quotation = null;
            if (! empty($data['quotation_id'])) {
                $quotation = VendorQuotation::where('company_id', $company->id)
                    ->where('status', 'Approved')
                    ->findOrFail($data['quotation_id']);
            }

            $purchaseOrder = PurchaseOrder::create([
                ...$data,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'po_number' => $this->generateSequentialNumber(PurchaseOrder::class, 'PO', $company, $financialYear),
                'status' => 'Draft',
                'created_by' => $creator->id,
            ]);

            $this->syncItems($purchaseOrder, $items);

            if ($quotation) {
                $quotation->update(['status' => 'Converted']);
            }

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
            unset($data['items'], $data['quotation_id']);

            $purchaseOrder->update($data);
            $this->syncItems($purchaseOrder, $items);

            $this->auditLog->log('Purchase Order Updated', 'Purchase Order', $purchaseOrder, $old, $purchaseOrder->toArray());

            return $purchaseOrder;
        });
    }

    public function send(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'Draft') {
            throw new RuntimeException('Only draft purchase orders can be sent.');
        }

        if ($purchaseOrder->items()->count() === 0) {
            throw new RuntimeException('Add at least one line item before sending.');
        }

        $purchaseOrder->update(['status' => 'Sent']);
        $this->auditLog->log('Purchase Order Sent', 'Purchase Order', $purchaseOrder, null, ['status' => 'Sent']);

        return $purchaseOrder;
    }

    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (! in_array($purchaseOrder->status, ['Draft', 'Sent'], true)) {
            throw new RuntimeException('Only draft or sent purchase orders can be cancelled.');
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

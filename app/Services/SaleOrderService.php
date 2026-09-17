<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\SaleOrder;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleOrderService
{
    use GeneratesSequentialNumbers;

    public function __construct(private AuditLogService $auditLog) {}

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): SaleOrder
    {
        return DB::transaction(function () use ($data, $company, $financialYear, $creator) {
            $items = $data['items'];
            unset($data['items']);

            $order = SaleOrder::create([
                ...$data,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'order_number' => $this->generateSequentialNumber(SaleOrder::class, 'SO', $company, $financialYear),
                'status' => 'Draft',
                'created_by' => $creator->id,
            ]);

            $this->syncItems($order, $items);

            $this->auditLog->log('Sale Order Created', 'Sale Order', $order, null, $order->toArray());

            return $order;
        });
    }

    public function update(SaleOrder $order, array $data): SaleOrder
    {
        if ($order->status !== 'Draft') {
            throw new RuntimeException('Only draft sale orders can be edited.');
        }

        return DB::transaction(function () use ($order, $data) {
            $old = $order->toArray();
            $items = $data['items'];
            unset($data['items']);

            $order->update($data);
            $this->syncItems($order, $items);

            $this->auditLog->log('Sale Order Updated', 'Sale Order', $order, $old, $order->toArray());

            return $order;
        });
    }

    /**
     * Single-actor, no approval gate -- this is an order taken from a customer,
     * not an internal spending commitment needing a second sign-off (unlike
     * Vendor Quotation approval on the purchase side).
     */
    public function confirm(SaleOrder $order): SaleOrder
    {
        if ($order->status !== 'Draft') {
            throw new RuntimeException('Only draft sale orders can be confirmed.');
        }

        if ($order->items()->count() === 0) {
            throw new RuntimeException('Add at least one line item before confirming.');
        }

        $order->update(['status' => 'Confirmed']);
        $this->auditLog->log('Sale Order Confirmed', 'Sale Order', $order, null, ['status' => 'Confirmed']);

        return $order;
    }

    public function cancel(SaleOrder $order): SaleOrder
    {
        if (! in_array($order->status, ['Draft', 'Confirmed'], true)) {
            throw new RuntimeException('Only a draft or confirmed sale order can be cancelled.');
        }

        $order->update(['status' => 'Cancelled']);
        $this->auditLog->log('Sale Order Cancelled', 'Sale Order', $order, null, ['status' => 'Cancelled']);

        return $order;
    }

    private function syncItems(SaleOrder $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $item) {
            $quantity = round((float) $item['quantity'], 2);
            $unitPrice = round((float) $item['unit_price'], 2);

            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => round($quantity * $unitPrice, 2),
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}

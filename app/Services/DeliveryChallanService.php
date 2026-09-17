<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DeliveryChallan;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeliveryChallanService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private StockMovementService $stockMovementService,
    ) {}

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): DeliveryChallan
    {
        return DB::transaction(function () use ($data, $company, $financialYear, $creator) {
            $items = $data['items'];
            unset($data['items']);

            $challan = DeliveryChallan::create([
                ...$data,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'challan_number' => $this->generateSequentialNumber(DeliveryChallan::class, 'DC', $company, $financialYear),
                'status' => 'Draft',
                'created_by' => $creator->id,
            ]);

            $this->syncItems($challan, $items);

            $this->auditLog->log('Delivery Challan Created', 'Delivery Challan', $challan, null, $challan->toArray());

            return $challan;
        });
    }

    public function update(DeliveryChallan $challan, array $data): DeliveryChallan
    {
        if ($challan->status !== 'Draft') {
            throw new RuntimeException('Only a draft delivery challan can be edited.');
        }

        return DB::transaction(function () use ($challan, $data) {
            $old = $challan->toArray();
            $items = $data['items'];
            unset($data['items'], $data['sale_order_id']);

            $challan->update($data);
            $this->syncItems($challan, $items);

            $this->auditLog->log('Delivery Challan Updated', 'Delivery Challan', $challan, $old, $challan->toArray());

            return $challan;
        });
    }

    /**
     * The single place stock actually decrements for the challan path -- once
     * Completed, a challan can't be un-completed (matches Goods Receipt's rule);
     * a correction after this point is a Stock Adjustment, not a reversal.
     */
    public function complete(DeliveryChallan $challan, User $actor): DeliveryChallan
    {
        if ($challan->status !== 'Draft') {
            throw new RuntimeException('Only a draft delivery challan can be completed.');
        }

        if ($challan->items()->count() === 0) {
            throw new RuntimeException('Add at least one line item before completing.');
        }

        return DB::transaction(function () use ($challan, $actor) {
            $challan->load(['items.product', 'company', 'financialYear']);

            foreach ($challan->items as $item) {
                $this->stockMovementService->postOut(
                    $challan->company,
                    $challan->financialYear,
                    $item->product,
                    (float) $item->quantity,
                    $challan->challan_date->toDateString(),
                    $item,
                    $challan->challan_number,
                    null,
                    $actor,
                );
            }

            $challan->update(['status' => 'Completed']);

            $this->auditLog->log('Delivery Challan Completed', 'Delivery Challan', $challan, null, ['status' => 'Completed']);

            return $challan;
        });
    }

    public function cancel(DeliveryChallan $challan): DeliveryChallan
    {
        if ($challan->status !== 'Draft') {
            throw new RuntimeException('Only a draft delivery challan can be cancelled.');
        }

        $challan->update(['status' => 'Cancelled']);
        $this->auditLog->log('Delivery Challan Cancelled', 'Delivery Challan', $challan, null, ['status' => 'Cancelled']);

        return $challan;
    }

    private function syncItems(DeliveryChallan $challan, array $items): void
    {
        $challan->items()->delete();

        foreach ($items as $item) {
            $challan->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => round((float) $item['quantity'], 2),
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}

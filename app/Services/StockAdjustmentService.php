<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockAdjustmentService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private StockMovementService $stockMovementService,
        private StockLevelService $stockLevelService,
    ) {}

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): StockAdjustment
    {
        return DB::transaction(function () use ($data, $company, $financialYear, $creator) {
            if ($data['type'] === 'Increase') {
                // A blank cost defaults to the current derived average, which is
                // mathematically neutral (adding quantity at the existing average
                // leaves the average unchanged) — a real "Opening Stock" entry
                // overrides it with the actual historical cost.
                if (! isset($data['unit_cost']) || $data['unit_cost'] === '' || $data['unit_cost'] === null) {
                    $data['unit_cost'] = $this->stockLevelService->averageCostFor(Product::findOrFail($data['product_id']));
                }
            } else {
                // Decrease's cost is derived live by StockMovementService::postOut()
                // at approval time, same as any other stock-out — never user-entered.
                $data['unit_cost'] = null;
            }

            $adjustment = StockAdjustment::create([
                ...$data,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'adjustment_number' => $this->generateSequentialNumber(StockAdjustment::class, 'ADJ', $company, $financialYear),
                'status' => 'Pending',
                'created_by' => $creator->id,
            ]);

            $this->auditLog->log('Stock Adjustment Created', 'Stock Adjustment', $adjustment, null, $adjustment->toArray());

            return $adjustment;
        });
    }

    public function decide(StockAdjustment $adjustment, User $approver, string $decision, ?string $comments = null): StockAdjustment
    {
        if ($adjustment->status !== 'Pending') {
            throw new RuntimeException('Only pending stock adjustments can be approved or rejected.');
        }

        if ($adjustment->created_by === $approver->id) {
            throw new RuntimeException('You cannot approve or reject a stock adjustment you created yourself.');
        }

        $adjustment->load('product', 'company', 'financialYear');

        if ($decision === 'Approved' && $adjustment->type === 'Decrease') {
            $currentStock = $this->stockLevelService->currentStockFor($adjustment->product);

            if ((float) $adjustment->quantity > $currentStock + 0.01) {
                throw new RuntimeException("Cannot decrease stock by {$adjustment->quantity}: only {$currentStock} currently in stock.");
            }
        }

        return DB::transaction(function () use ($adjustment, $approver, $decision, $comments) {
            $adjustment->update([
                'status' => $decision,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'approval_comments' => $comments,
            ]);

            if ($decision === 'Approved' && $adjustment->type === 'Increase') {
                $this->stockMovementService->postIn(
                    $adjustment->company,
                    $adjustment->financialYear,
                    $adjustment->product,
                    (float) $adjustment->quantity,
                    $adjustment->adjustment_date->toDateString(),
                    $adjustment,
                    $adjustment->adjustment_number,
                    $adjustment->reason,
                    $approver,
                    (float) $adjustment->unit_cost,
                );
            } elseif ($decision === 'Approved') {
                $this->stockMovementService->postOut(
                    $adjustment->company,
                    $adjustment->financialYear,
                    $adjustment->product,
                    (float) $adjustment->quantity,
                    $adjustment->adjustment_date->toDateString(),
                    $adjustment,
                    $adjustment->adjustment_number,
                    $adjustment->reason,
                    $approver,
                );
            }

            $this->auditLog->log("Stock Adjustment {$decision}", 'Stock Adjustment', $adjustment, null, ['status' => $decision, 'comments' => $comments]);

            return $adjustment;
        });
    }
}

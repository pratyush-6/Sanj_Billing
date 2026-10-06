<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class StockAdjustmentService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private StockMovementService $stockMovementService,
        private StockLevelService $stockLevelService,
    ) {}

    public function create(array $data, Company $company, Branch $branch, FinancialYear $financialYear, User $creator): StockAdjustment
    {
        return DB::transaction(function () use ($data, $company, $branch, $financialYear, $creator) {
            if ($data['type'] === 'Increase') {
                // A blank cost defaults to this branch's current average, which is
                // mathematically neutral (adding quantity at the existing average
                // leaves the average unchanged). A branch with no stock has no average
                // to fall back on, so the cost must be entered explicitly.
                if (! isset($data['unit_cost']) || $data['unit_cost'] === '' || $data['unit_cost'] === null) {
                    $average = $this->stockLevelService->averageCostFor(Product::findOrFail($data['product_id']), $branch->id);

                    if ($average <= 0) {
                        throw new RuntimeException('This branch has no stock of this product to average. Enter a unit cost.');
                    }

                    $data['unit_cost'] = $average;
                }
            } else {
                // Decrease's cost is derived live by StockMovementService::postOut()
                // at approval time, same as any other stock-out — never user-entered.
                $data['unit_cost'] = null;
            }

            $adjustment = StockAdjustment::create([
                ...$data,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'financial_year_id' => $financialYear->id,
                'adjustment_number' => $this->generateSequentialNumber(StockAdjustment::class, 'ADJ', $company, $financialYear),
                'status' => 'Pending',
                'created_by' => $creator->id,
            ]);

            $this->auditLog->log('Stock Adjustment Created', 'Stock Adjustment', $adjustment, null, $adjustment->toArray());

            return $adjustment;
        });
    }

    /**
     * Moves stock that predates branches (branch_id NULL) into a branch. Posted as one
     * transaction: a Decrease out of the unassigned bucket, then an Increase into the
     * branch at the exact unit cost that left, so total inventory value is unchanged.
     * Both rows share an assignment_group so they can be read as one action.
     */
    public function assignUnassigned(Company $company, FinancialYear $financialYear, Product $product, float $quantity, Branch $branch, string $adjustmentDate, string $reason, User $actor): StockAdjustment
    {
        return DB::transaction(function () use ($company, $financialYear, $product, $quantity, $branch, $adjustmentDate, $reason, $actor) {
            $group = (string) Str::uuid();
            $now = now();

            $decrease = StockAdjustment::create([
                'company_id' => $company->id,
                'branch_id' => null,
                'financial_year_id' => $financialYear->id,
                'product_id' => $product->id,
                'adjustment_number' => $this->generateSequentialNumber(StockAdjustment::class, 'ADJ', $company, $financialYear),
                'adjustment_date' => $adjustmentDate,
                'type' => 'Decrease',
                'reason' => $reason,
                'quantity' => $quantity,
                'unit_cost' => null,
                'status' => 'Approved',
                'notes' => "Assigned to {$branch->name}",
                'created_by' => $actor->id,
                'approved_by' => $actor->id,
                'approved_at' => $now,
                'assignment_group' => $group,
            ]);

            $outMovement = $this->stockMovementService->postOut(
                $company, $financialYear, null, $product, $quantity, $adjustmentDate, $decrease, $decrease->adjustment_number, $reason, $actor,
            );

            $increase = StockAdjustment::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'financial_year_id' => $financialYear->id,
                'product_id' => $product->id,
                'adjustment_number' => $this->generateSequentialNumber(StockAdjustment::class, 'ADJ', $company, $financialYear),
                'adjustment_date' => $adjustmentDate,
                'type' => 'Increase',
                'reason' => $reason,
                'quantity' => $quantity,
                'unit_cost' => $outMovement->unit_cost,
                'status' => 'Approved',
                'notes' => 'Assigned from unassigned stock',
                'created_by' => $actor->id,
                'approved_by' => $actor->id,
                'approved_at' => $now,
                'assignment_group' => $group,
            ]);

            $this->stockMovementService->postIn(
                $company, $financialYear, $branch->id, $product, $quantity, $adjustmentDate, $increase, $increase->adjustment_number, $reason, $actor, (float) $outMovement->unit_cost,
            );

            $this->auditLog->log('Stock Assigned to Branch', 'Stock Adjustment', $increase, null, [
                'assignment_group' => $group,
                'branch_id' => $branch->id,
                'quantity' => $quantity,
            ]);

            return $increase;
        });
    }

    public function decide(StockAdjustment $adjustment, User $approver, string $decision, ?string $comments = null): StockAdjustment
    {
        if ($adjustment->status !== 'Pending') {
            throw new RuntimeException('Only pending stock adjustments can be approved or rejected.');
        }

        $adjustment->load('product', 'company', 'financialYear');

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
                    $adjustment->branch_id,
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
                    $adjustment->branch_id,
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

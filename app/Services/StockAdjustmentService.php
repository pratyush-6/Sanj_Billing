<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
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

            if ($decision === 'Approved') {
                $method = $adjustment->type === 'Increase' ? 'postIn' : 'postOut';

                $this->stockMovementService->{$method}(
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

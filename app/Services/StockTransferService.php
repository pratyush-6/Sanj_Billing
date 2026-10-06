<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private StockMovementService $stockMovementService,
    ) {}

    /**
     * Moves stock between two branches of the same company in one transaction. Stock
     * leaves the source at its weighted-average cost and arrives at the destination at
     * that same cost, so the company's total inventory value does not change.
     *
     * @param  array<int, array{product_id: int, quantity: float|string}>  $lines
     */
    public function create(Company $company, FinancialYear $financialYear, Branch $from, Branch $to, string $transferDate, array $lines, ?string $notes, User $actor): StockTransfer
    {
        if ($from->id === $to->id) {
            throw new RuntimeException('Choose two different branches for a transfer.');
        }

        if ($from->company_id !== $company->id || $to->company_id !== $company->id) {
            throw new RuntimeException('Both branches must belong to the current company.');
        }

        if (empty($lines)) {
            throw new RuntimeException('Add at least one product to transfer.');
        }

        return DB::transaction(function () use ($company, $financialYear, $from, $to, $transferDate, $lines, $notes, $actor) {
            $transfer = StockTransfer::create([
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'from_branch_id' => $from->id,
                'to_branch_id' => $to->id,
                'transfer_number' => $this->generateSequentialNumber(StockTransfer::class, 'STF', $company, $financialYear),
                'transfer_date' => $transferDate,
                'status' => 'Completed',
                'notes' => $notes,
                'created_by' => $actor->id,
            ]);

            foreach ($lines as $line) {
                $product = Product::where('company_id', $company->id)->findOrFail($line['product_id']);
                $quantity = round((float) $line['quantity'], 2);

                $outMovement = $this->stockMovementService->postOut(
                    $company, $financialYear, $from->id, $product, $quantity, $transferDate, $transfer, $transfer->transfer_number, "Transfer to {$to->name}", $actor,
                );

                $unitCost = (float) $outMovement->unit_cost;

                $this->stockMovementService->postIn(
                    $company, $financialYear, $to->id, $product, $quantity, $transferDate, $transfer, $transfer->transfer_number, "Transfer from {$from->name}", $actor, $unitCost,
                );

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => round($unitCost * $quantity, 2),
                ]);
            }

            $this->auditLog->log('Stock Transferred', 'Stock Transfer', $transfer, null, [
                'from_branch' => $from->name,
                'to_branch' => $to->name,
                'lines' => count($lines),
            ]);

            return $transfer;
        });
    }
}

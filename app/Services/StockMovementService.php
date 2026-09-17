<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockMovementService
{
    public function __construct(private StockLevelService $stockLevelService) {}

    /**
     * $unitCost is the caller's known cost basis for this inflow (e.g. the PO's
     * unit_price at goods-receipt time) — postIn() trusts it as given, it does not
     * derive one.
     */
    public function postIn(
        Company $company,
        FinancialYear $financialYear,
        Product $product,
        float $quantity,
        string $movementDate,
        ?Model $source,
        ?string $referenceNumber,
        ?string $notes,
        User $creator,
        ?float $unitCost = null,
    ): StockMovement {
        $unitCost = $unitCost !== null ? round($unitCost, 4) : null;
        $totalCost = $unitCost !== null ? round($unitCost * $quantity, 2) : null;

        return $this->post($company, $financialYear, $product, 'In', $quantity, $movementDate, $source, $referenceNumber, $notes, $creator, $unitCost, $totalCost);
    }

    /**
     * The single choke point for outbound cost: computes the current weighted-average
     * cost itself (StockLevelService::averageCostFor(), row-locked for the rest of
     * this transaction) and snapshots it onto the movement — callers never pass a
     * cost in, so every Out movement in the app is valued the same consistent way.
     */
    public function postOut(
        Company $company,
        FinancialYear $financialYear,
        Product $product,
        float $quantity,
        string $movementDate,
        ?Model $source,
        ?string $referenceNumber,
        ?string $notes,
        User $creator,
    ): StockMovement {
        return DB::transaction(function () use ($company, $financialYear, $product, $quantity, $movementDate, $source, $referenceNumber, $notes, $creator) {
            $unitCost = $this->stockLevelService->averageCostFor($product, lock: true);
            $totalCost = round($unitCost * $quantity, 2);

            return $this->post($company, $financialYear, $product, 'Out', $quantity, $movementDate, $source, $referenceNumber, $notes, $creator, $unitCost, $totalCost);
        });
    }

    private function post(
        Company $company,
        FinancialYear $financialYear,
        Product $product,
        string $direction,
        float $quantity,
        string $movementDate,
        ?Model $source,
        ?string $referenceNumber,
        ?string $notes,
        User $creator,
        ?float $unitCost,
        ?float $totalCost,
    ): StockMovement {
        if ($financialYear->is_locked) {
            throw new RuntimeException('This financial year is locked. Stock cannot be moved.');
        }

        if ($quantity <= 0) {
            throw new RuntimeException('Stock movement quantity must be greater than zero.');
        }

        return StockMovement::create([
            'company_id' => $company->id,
            'financial_year_id' => $financialYear->id,
            'product_id' => $product->id,
            'direction' => $direction,
            'quantity' => round($quantity, 2),
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'movement_date' => $movementDate,
            'reference_number' => $referenceNumber,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'notes' => $notes,
            'created_by' => $creator->id,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class StockMovementService
{
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
    ): StockMovement {
        return $this->post($company, $financialYear, $product, 'In', $quantity, $movementDate, $source, $referenceNumber, $notes, $creator);
    }

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
        return $this->post($company, $financialYear, $product, 'Out', $quantity, $movementDate, $source, $referenceNumber, $notes, $creator);
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
            'movement_date' => $movementDate,
            'reference_number' => $referenceNumber,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'notes' => $notes,
            'created_by' => $creator->id,
        ]);
    }
}

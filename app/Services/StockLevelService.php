<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StockLevelService
{
    /**
     * $branchId null means the unassigned bucket (movements recorded before branches
     * existed). $allBranches ignores the branch dimension entirely — used by
     * company-wide reports only.
     *
     * Mirrors AccountingService::computeBalances() exactly: one grouped query for
     * however many products are asked for, so listing pages never N+1 per row.
     */
    public function currentStock(Collection $products, ?int $branchId, bool $allBranches = false, ?string $asOfDate = null): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        $sums = $this->scopeToBranch(StockMovement::query(), $branchId, $allBranches)
            ->whereIn('product_id', $products->pluck('id'))
            ->when($asOfDate, fn ($query) => $query->whereDate('movement_date', '<=', $asOfDate))
            ->selectRaw("product_id, SUM(CASE WHEN direction = 'In' THEN quantity ELSE -quantity END) as net_quantity")
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        return $products->mapWithKeys(fn ($product) => [
            $product->id => (float) ($sums->get($product->id)?->net_quantity ?? 0),
        ])->all();
    }

    public function currentStockFor(Product $product, ?int $branchId, bool $allBranches = false, ?string $asOfDate = null): float
    {
        return $this->currentStock(collect([$product]), $branchId, $allBranches, $asOfDate)[$product->id] ?? 0.0;
    }

    /**
     * Weighted-average cost of the stock currently on hand at one branch, derived — not
     * stored — from every movement's own unit_cost/total_cost snapshot: remaining value is
     * SUM(In total_cost) - SUM(Out total_cost), remaining qty is the same net-quantity
     * calculation as currentStock(). Each branch averages only its own purchases, so a
     * sale's COGS reflects the cost of the stock that branch actually holds.
     */
    public function averageCostFor(Product $product, ?int $branchId): float
    {
        $row = $this->scopeToBranch(StockMovement::query(), $branchId, false)
            ->where('product_id', $product->id)
            ->selectRaw("
                SUM(CASE WHEN direction = 'In' THEN quantity ELSE -quantity END) as net_quantity,
                SUM(CASE WHEN direction = 'In' THEN total_cost ELSE -total_cost END) as net_value
            ")
            ->first();

        $netQuantity = (float) ($row->net_quantity ?? 0);

        if ($netQuantity <= 0) {
            return 0.0;
        }

        return round((float) ($row->net_value ?? 0) / $netQuantity, 4);
    }

    private function scopeToBranch(Builder $query, ?int $branchId, bool $allBranches): Builder
    {
        if ($allBranches) {
            return $query;
        }

        return $branchId === null
            ? $query->whereNull('branch_id')
            : $query->where('branch_id', $branchId);
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

class StockLevelService
{
    /**
     * Mirrors AccountingService::computeBalances() exactly: one grouped query for
     * however many products are asked for, so listing pages never N+1 per row.
     */
    public function currentStock(Collection $products, ?string $asOfDate = null): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        $sums = StockMovement::whereIn('product_id', $products->pluck('id'))
            ->when($asOfDate, fn ($query) => $query->whereDate('movement_date', '<=', $asOfDate))
            ->selectRaw("product_id, SUM(CASE WHEN direction = 'In' THEN quantity ELSE -quantity END) as net_quantity")
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        return $products->mapWithKeys(fn ($product) => [
            $product->id => (float) ($sums->get($product->id)?->net_quantity ?? 0),
        ])->all();
    }

    public function currentStockFor(Product $product, ?string $asOfDate = null): float
    {
        return $this->currentStock(collect([$product]), $asOfDate)[$product->id] ?? 0.0;
    }

    /**
     * Weighted-average cost of the stock currently on hand, derived — not stored —
     * from every movement's own unit_cost/total_cost snapshot: remaining value is
     * SUM(In total_cost) - SUM(Out total_cost), remaining qty is the same net-quantity
     * calculation as currentStock(). This stays correct through any sequence of
     * inflows/outflows because every prior Out already snapshotted the average that
     * was correct *at the time it was written* — it does not need to be recomputed
     * from raw purchase history.
     *
     * Pass lock: true when this is about to inform a new Out movement's own cost
     * snapshot (see StockMovementService::postOut()) — it locks this product's
     * movement rows for the rest of the surrounding transaction, so two concurrent
     * sales of the same product can't both read the same stale average before
     * either commits.
     */
    public function averageCostFor(Product $product, bool $lock = false): float
    {
        $query = StockMovement::where('product_id', $product->id);

        if ($lock) {
            $query->lockForUpdate();
        }

        $row = $query->selectRaw("
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
}

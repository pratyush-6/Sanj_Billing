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
}

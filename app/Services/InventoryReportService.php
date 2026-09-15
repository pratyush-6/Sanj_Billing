<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryReportService
{
    public function __construct(private StockLevelService $stockLevelService) {}

    public function lowStock(Company $company): Collection
    {
        $products = Product::where('company_id', $company->id)->where('status', 'active')->get();
        $stockLevels = $this->stockLevelService->currentStock($products);

        return $products
            ->map(fn ($product) => (object) [
                'product' => $product,
                'current_stock' => $stockLevels[$product->id] ?? 0.0,
            ])
            ->filter(fn ($row) => $row->current_stock <= (float) $row->product->min_stock_level)
            ->sortBy(fn ($row) => $row->current_stock - (float) $row->product->min_stock_level)
            ->values();
    }

    public function movementHistory(Company $company, ?int $productId = null, ?string $from = null, ?string $to = null): LengthAwarePaginator
    {
        return StockMovement::where('company_id', $company->id)
            ->with(['product', 'creator'])
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($from, fn ($query) => $query->whereDate('movement_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('movement_date', '<=', $to))
            ->latest('movement_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();
    }
}

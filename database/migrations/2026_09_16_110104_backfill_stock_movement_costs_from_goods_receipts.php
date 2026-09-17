<?php

use App\Models\GoodsReceipt;
use App\Models\StockMovement;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Every 'In' stock movement created before this chunk has a null unit_cost.
     * Left as-is, that silently understates the weighted-average cost calculation
     * (a movement with quantity but no value dilutes the average toward zero) the
     * moment the new cost-tracking code goes live. The correct cost is still
     * recoverable for GoodsReceipt-sourced movements via the PO price they were
     * actually received at, so backfill it rather than leaving the gap.
     */
    public function up(): void
    {
        StockMovement::where('direction', 'In')
            ->whereNull('unit_cost')
            ->where('source_type', GoodsReceipt::class)
            ->get()
            ->each(function (StockMovement $movement) {
                $goodsReceipt = GoodsReceipt::with('items.purchaseOrderItem')->find($movement->source_id);
                if (! $goodsReceipt) {
                    return;
                }

                $matchingItems = $goodsReceipt->items->filter(
                    fn ($item) => $item->purchaseOrderItem?->product_id === $movement->product_id
                );

                $totalQty = $matchingItems->sum('quantity_received');
                if ($totalQty <= 0) {
                    return;
                }

                $weightedPriceSum = $matchingItems->sum(
                    fn ($item) => (float) $item->quantity_received * (float) $item->purchaseOrderItem->unit_price
                );

                $unitCost = round($weightedPriceSum / $totalQty, 4);

                $movement->update([
                    'unit_cost' => $unitCost,
                    'total_cost' => round($unitCost * (float) $movement->quantity, 2),
                ]);
            });
    }

    public function down(): void
    {
        // No-op: this only fills in previously-null cost data, nothing to revert.
    }
};

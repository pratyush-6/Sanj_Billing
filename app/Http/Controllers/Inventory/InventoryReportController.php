<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\InventoryReportService;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    public function __construct(private InventoryReportService $inventoryReportService) {}

    public function lowStock()
    {
        $company = current_company_or_fail();

        return view('inventory.reports.low-stock', [
            'rows' => $this->inventoryReportService->lowStock($company),
        ]);
    }

    public function movements(Request $request)
    {
        $company = current_company_or_fail();

        return view('inventory.reports.movements', [
            'movements' => $this->inventoryReportService->movementHistory(
                $company,
                $request->integer('product_id') ?: null,
                $request->input('date_from'),
                $request->input('date_to'),
            ),
            'products' => Product::where('company_id', $company->id)->orderBy('name')->get(),
        ]);
    }
}

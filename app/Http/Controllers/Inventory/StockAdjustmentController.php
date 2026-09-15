<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentDecisionRequest;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class StockAdjustmentController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private StockAdjustmentService $stockAdjustmentService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $adjustments = StockAdjustment::where('company_id', $companyId)
            ->with(['product', 'creator', 'approver'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('stock-adjustments.index', [
            'adjustments' => $adjustments,
            'statuses' => config('inventory.stock_adjustment_statuses'),
        ]);
    }

    public function create()
    {
        return $this->formData();
    }

    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('stock-adjustments.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $adjustment = $this->stockAdjustmentService->create($request->validated(), $company, $financialYear, Auth::user());

        return redirect()->route('stock-adjustments.show', $adjustment)->with('status', "Stock Adjustment {$adjustment->adjustment_number} submitted for approval.");
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $this->ensureBelongsToCurrentCompany($stockAdjustment);

        $stockAdjustment->load(['product', 'creator', 'approver']);

        return view('stock-adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    public function decide(StockAdjustmentDecisionRequest $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($stockAdjustment);

        $decision = $request->string('decision')->toString();

        try {
            $this->stockAdjustmentService->decide($stockAdjustment, Auth::user(), $decision, $request->input('comments'));
        } catch (RuntimeException $exception) {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-adjustments.show', $stockAdjustment)->with('status', "Stock adjustment {$decision}.");
    }

    private function formData()
    {
        $company = current_company_or_fail();

        return view('stock-adjustments.form', [
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
            'types' => config('inventory.adjustment_types'),
            'reasons' => config('inventory.adjustment_reasons'),
        ]);
    }
}

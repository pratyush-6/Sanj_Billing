<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentDecisionRequest;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\BranchContextService;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

class StockAdjustmentController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private StockAdjustmentService $stockAdjustmentService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $adjustments = app(BranchContextService::class)->applyTo(StockAdjustment::where('company_id', $companyId))
            ->with(['product', 'creator', 'approver', 'branch'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('stock-adjustments.index', [
            'adjustments' => $adjustments,
            'statuses' => config('inventory.stock_adjustment_statuses'),
            'branch' => current_branch(),
            'products' => Product::where('company_id', $companyId)->where('status', 'active')->orderBy('name')->get(),
            'reasons' => config('inventory.adjustment_reasons'),
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

        try {
            $adjustment = $this->stockAdjustmentService->create($request->validated(), $company, current_branch_or_fail(), $financialYear, Auth::user());
        } catch (RuntimeException $exception) {
            return redirect()->route('stock-adjustments.create')->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-adjustments.show', $adjustment)->with('status', "Stock Adjustment {$adjustment->adjustment_number} submitted for approval.");
    }

    public function assignUnassigned(Request $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('stock-adjustments.index')->with('error', 'No active financial year. Please set one up first.');
        }

        $data = $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->where('company_id', $company->id)],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'adjustment_date' => ['required', 'date'],
            'reason' => ['required', Rule::in(config('inventory.adjustment_reasons'))],
        ]);

        try {
            $adjustment = $this->stockAdjustmentService->assignUnassigned(
                $company,
                $financialYear,
                Product::findOrFail($data['product_id']),
                (float) $data['quantity'],
                current_branch_or_fail(),
                $data['adjustment_date'],
                $data['reason'],
                Auth::user(),
            );
        } catch (RuntimeException $exception) {
            return redirect()->route('stock-adjustments.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-adjustments.show', $adjustment)->with('status', 'Unassigned stock moved into this branch.');
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
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->with(['unit', 'secondaryUnit'])->orderBy('name')->get(),
            'types' => config('inventory.adjustment_types'),
            'reasons' => config('inventory.adjustment_reasons'),
        ]);
    }
}

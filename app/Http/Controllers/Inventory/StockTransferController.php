<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Services\BranchContextService;
use App\Services\StockTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

class StockTransferController extends Controller
{
    public function __construct(
        private StockTransferService $stockTransferService,
        private BranchContextService $branchContext,
    ) {}

    public function index()
    {
        $companyId = current_company_or_fail()->id;

        $transfers = StockTransfer::where('company_id', $companyId)
            ->when(! $this->branchContext->isAllBranches(), function ($query) {
                $branchId = current_branch()?->id;

                $query->where(fn ($q) => $q->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId));
            })
            ->with(['fromBranch', 'toBranch', 'creator'])
            ->latest('transfer_date')
            ->latest('id')
            ->paginate(20);

        return view('stock-transfers.index', ['transfers' => $transfers]);
    }

    public function create()
    {
        $company = current_company_or_fail();

        return view('stock-transfers.form', [
            'from' => current_branch(),
            'destinations' => Branch::where('company_id', $company->id)
                ->where('status', 'active')
                ->where('id', '!=', current_branch()?->id)
                ->orderBy('name')
                ->get(),
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('stock-transfers.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $data = $request->validate([
            'to_branch_id' => ['required', Rule::exists('branches', 'id')->where('company_id', $company->id)->where('status', 'active')],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('company_id', $company->id)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        $to = Branch::where('company_id', $company->id)->findOrFail($data['to_branch_id']);

        try {
            $transfer = $this->stockTransferService->create(
                $company,
                $financialYear,
                current_branch_or_fail(),
                $to,
                $data['transfer_date'],
                $data['items'],
                $data['notes'] ?? null,
                Auth::user(),
            );
        } catch (RuntimeException $exception) {
            return redirect()->route('stock-transfers.create')->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-transfers.show', $transfer)->with('status', "Transfer {$transfer->transfer_number} completed.");
    }

    public function show(StockTransfer $stockTransfer)
    {
        abort_unless($stockTransfer->company_id === current_company_or_fail()->id, 404);

        $stockTransfer->load(['items.product', 'fromBranch', 'toBranch', 'creator']);

        return view('stock-transfers.show', ['transfer' => $stockTransfer]);
    }
}

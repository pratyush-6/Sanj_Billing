<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\VendorQuotationRequest;
use App\Models\Party;
use App\Models\Product;
use App\Models\VendorQuotation;
use App\Services\VendorQuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class VendorQuotationController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private VendorQuotationService $quotationService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $quotations = VendorQuotation::where('company_id', $companyId)
            ->with(['vendor', 'creator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('vendor_id'), fn ($query) => $query->where('vendor_id', $request->integer('vendor_id')))
            ->latest('quotation_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('vendor-quotations.index', [
            'quotations' => $quotations,
            'vendors' => Party::where('company_id', $companyId)->where('is_vendor', true)->orderBy('name')->get(),
            'statuses' => config('inventory.quotation_statuses'),
        ]);
    }

    public function create()
    {
        return $this->formData();
    }

    public function store(VendorQuotationRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('vendor-quotations.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $quotation = $this->quotationService->create($request->validated(), $company, $financialYear, Auth::user());

        return redirect()->route('vendor-quotations.show', $quotation)->with('status', "Quotation {$quotation->quotation_number} saved as draft.");
    }

    public function show(VendorQuotation $quotation)
    {
        $this->ensureBelongsToCurrentCompany($quotation);

        $quotation->load(['vendor', 'creator', 'items.product', 'approvals.approver', 'purchaseOrders']);

        return view('vendor-quotations.show', ['quotation' => $quotation]);
    }

    public function edit(VendorQuotation $quotation)
    {
        $this->ensureBelongsToCurrentCompany($quotation);

        if ($quotation->status !== 'Draft') {
            return redirect()->route('vendor-quotations.show', $quotation)->with('error', 'Only draft quotations can be edited.');
        }

        return $this->formData($quotation);
    }

    public function update(VendorQuotationRequest $request, VendorQuotation $quotation): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($quotation);

        try {
            $this->quotationService->update($quotation, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('vendor-quotations.edit', $quotation)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor-quotations.show', $quotation)->with('status', 'Quotation updated.');
    }

    public function submit(VendorQuotation $quotation): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($quotation);

        try {
            $this->quotationService->submit($quotation);
        } catch (RuntimeException $exception) {
            return redirect()->route('vendor-quotations.show', $quotation)->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor-quotations.show', $quotation)->with('status', 'Quotation submitted for approval.');
    }

    public function compare(Request $request)
    {
        $companyId = current_company()?->id;

        $quotationIds = collect($request->query('ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id);

        $quotations = VendorQuotation::where('company_id', $companyId)
            ->whereIn('id', $quotationIds)
            ->with(['vendor', 'items.product'])
            ->get();

        return view('vendor-quotations.compare', [
            'quotations' => $quotations,
            'allQuotations' => VendorQuotation::where('company_id', $companyId)
                ->whereIn('status', ['Submitted', 'Approved'])
                ->with('vendor')
                ->latest('quotation_date')
                ->get(),
        ]);
    }

    private function formData(?VendorQuotation $quotation = null)
    {
        $company = current_company_or_fail();

        return view('vendor-quotations.form', [
            'quotation' => $quotation?->load('items.product'),
            'vendors' => Party::where('company_id', $company->id)->where('is_vendor', true)->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}

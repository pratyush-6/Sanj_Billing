<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\Party;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class PurchaseOrderController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private PurchaseOrderService $purchaseOrderService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $purchaseOrders = scope_to_branch(PurchaseOrder::where('company_id', $companyId))
            ->with(['vendor', 'creator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('vendor_id'), fn ($query) => $query->where('vendor_id', $request->integer('vendor_id')))
            ->latest('po_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'vendors' => Party::where('company_id', $companyId)->where('is_vendor', true)->orderBy('name')->get(),
            'statuses' => config('inventory.purchase_order_statuses'),
        ]);
    }

    public function create(Request $request)
    {
        return $this->formData();
    }

    public function store(PurchaseOrderRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('purchase-orders.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $purchaseOrder = $this->purchaseOrderService->create($request->validated(), $company, current_branch_or_fail(), $financialYear, Auth::user());

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('status', "Purchase Order {$purchaseOrder->po_number} saved as draft.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->ensureBelongsToCurrentCompany($purchaseOrder);

        $purchaseOrder->load(['vendor', 'creator', 'items.product', 'purchaseBill', 'expenses']);

        return view('purchase-orders.show', ['purchaseOrder' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->ensureBelongsToCurrentCompany($purchaseOrder);

        if ($purchaseOrder->status !== 'Draft') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', 'Only draft purchase orders can be edited.');
        }

        return $this->formData($purchaseOrder);
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseOrder);

        try {
            $this->purchaseOrderService->update($purchaseOrder, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-orders.edit', $purchaseOrder)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('status', 'Purchase order updated.');
    }

    public function submit(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseOrder);

        try {
            $this->purchaseOrderService->submit($purchaseOrder, Auth::user());
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('status', 'Purchase order submitted — stock updated.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseOrder);

        try {
            $this->purchaseOrderService->cancel($purchaseOrder);
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('status', 'Purchase order cancelled.');
    }

    private function formData(?PurchaseOrder $purchaseOrder = null)
    {
        $company = current_company_or_fail();

        return view('purchase-orders.form', [
            'purchaseOrder' => $purchaseOrder?->load('items.product'),
            'vendors' => Party::where('company_id', $company->id)->where('is_vendor', true)->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->with(['unit', 'secondaryUnit'])->orderBy('name')->get(),
        ]);
    }
}

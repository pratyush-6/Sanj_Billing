<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleOrderRequest;
use App\Models\Party;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Services\SaleOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SaleOrderController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private SaleOrderService $saleOrderService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $orders = SaleOrder::where('company_id', $companyId)
            ->with(['party', 'creator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('party_id'), fn ($query) => $query->where('party_id', $request->integer('party_id')))
            ->latest('order_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('sale-orders.index', [
            'orders' => $orders,
            'parties' => Party::where('company_id', $companyId)->where('is_customer', true)->orderBy('name')->get(),
            'statuses' => config('inventory.sale_order_statuses'),
        ]);
    }

    public function create()
    {
        return $this->formData();
    }

    public function store(SaleOrderRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('sale-orders.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $order = $this->saleOrderService->create($request->validated(), $company, $financialYear, Auth::user());

        return redirect()->route('sale-orders.show', $order)->with('status', "Sale Order {$order->order_number} saved as draft.");
    }

    public function show(SaleOrder $saleOrder)
    {
        $this->ensureBelongsToCurrentCompany($saleOrder);

        $saleOrder->load(['party', 'creator', 'items.product']);

        return view('sale-orders.show', ['order' => $saleOrder]);
    }

    public function edit(SaleOrder $saleOrder)
    {
        $this->ensureBelongsToCurrentCompany($saleOrder);

        if ($saleOrder->status !== 'Draft') {
            return redirect()->route('sale-orders.show', $saleOrder)->with('error', 'Only draft sale orders can be edited.');
        }

        return $this->formData($saleOrder);
    }

    public function update(SaleOrderRequest $request, SaleOrder $saleOrder): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleOrder);

        try {
            $this->saleOrderService->update($saleOrder, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-orders.edit', $saleOrder)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-orders.show', $saleOrder)->with('status', 'Sale order updated.');
    }

    public function confirm(SaleOrder $saleOrder): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleOrder);

        try {
            $this->saleOrderService->confirm($saleOrder);
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-orders.show', $saleOrder)->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-orders.show', $saleOrder)->with('status', 'Sale order confirmed.');
    }

    public function cancel(SaleOrder $saleOrder): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleOrder);

        try {
            $this->saleOrderService->cancel($saleOrder);
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-orders.show', $saleOrder)->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-orders.show', $saleOrder)->with('status', 'Sale order cancelled.');
    }

    private function formData(?SaleOrder $order = null)
    {
        $company = current_company_or_fail();

        return view('sale-orders.form', [
            'order' => $order?->load('items.product'),
            'parties' => Party::where('company_id', $company->id)->where('is_customer', true)->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}

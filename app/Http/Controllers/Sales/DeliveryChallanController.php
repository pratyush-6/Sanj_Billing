<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryChallanRequest;
use App\Models\DeliveryChallan;
use App\Models\Party;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Services\DeliveryChallanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class DeliveryChallanController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private DeliveryChallanService $deliveryChallanService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $challans = DeliveryChallan::where('company_id', $companyId)
            ->with(['party', 'saleOrder'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('challan_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('delivery-challans.index', [
            'challans' => $challans,
            'statuses' => config('inventory.delivery_challan_statuses'),
        ]);
    }

    public function create(Request $request)
    {
        $company = current_company_or_fail();
        $saleOrder = null;

        if ($request->filled('sale_order_id')) {
            $saleOrder = SaleOrder::where('company_id', $company->id)
                ->where('status', 'Confirmed')
                ->findOrFail($request->integer('sale_order_id'));

            $saleOrder->load(['items.product', 'party']);
        }

        return $this->formData(null, $saleOrder);
    }

    public function store(DeliveryChallanRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('delivery-challans.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $challan = $this->deliveryChallanService->create($request->validated(), $company, $financialYear, Auth::user());

        return redirect()->route('delivery-challans.show', $challan)->with('status', "Delivery Challan {$challan->challan_number} saved as draft.");
    }

    public function show(DeliveryChallan $deliveryChallan)
    {
        $this->ensureBelongsToCurrentCompany($deliveryChallan);

        $deliveryChallan->load(['party', 'saleOrder', 'creator', 'items.product']);

        return view('delivery-challans.show', ['challan' => $deliveryChallan]);
    }

    public function edit(DeliveryChallan $deliveryChallan)
    {
        $this->ensureBelongsToCurrentCompany($deliveryChallan);

        if ($deliveryChallan->status !== 'Draft') {
            return redirect()->route('delivery-challans.show', $deliveryChallan)->with('error', 'Only a draft delivery challan can be edited.');
        }

        $deliveryChallan->load(['items.product', 'saleOrder']);

        return $this->formData($deliveryChallan, $deliveryChallan->saleOrder);
    }

    public function update(DeliveryChallanRequest $request, DeliveryChallan $deliveryChallan): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($deliveryChallan);

        try {
            $this->deliveryChallanService->update($deliveryChallan, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('delivery-challans.edit', $deliveryChallan)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('delivery-challans.show', $deliveryChallan)->with('status', 'Delivery challan updated.');
    }

    public function complete(DeliveryChallan $deliveryChallan): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($deliveryChallan);

        try {
            $this->deliveryChallanService->complete($deliveryChallan, Auth::user());
        } catch (RuntimeException $exception) {
            return redirect()->route('delivery-challans.show', $deliveryChallan)->with('error', $exception->getMessage());
        }

        return redirect()->route('delivery-challans.show', $deliveryChallan)->with('status', 'Delivery challan completed and stock updated.');
    }

    public function cancel(DeliveryChallan $deliveryChallan): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($deliveryChallan);

        try {
            $this->deliveryChallanService->cancel($deliveryChallan);
        } catch (RuntimeException $exception) {
            return redirect()->route('delivery-challans.show', $deliveryChallan)->with('error', $exception->getMessage());
        }

        return redirect()->route('delivery-challans.show', $deliveryChallan)->with('status', 'Delivery challan cancelled.');
    }

    public function print(DeliveryChallan $deliveryChallan)
    {
        $this->ensureBelongsToCurrentCompany($deliveryChallan);

        $deliveryChallan->load(['party', 'company', 'items.product']);

        return Pdf::loadView('delivery-challans.print', ['challan' => $deliveryChallan])->stream("{$deliveryChallan->challan_number}.pdf");
    }

    private function formData(?DeliveryChallan $challan = null, ?SaleOrder $saleOrder = null)
    {
        $company = current_company_or_fail();

        return view('delivery-challans.form', [
            'challan' => $challan,
            'saleOrder' => $saleOrder,
            'parties' => Party::where('company_id', $company->id)->where('is_customer', true)->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}

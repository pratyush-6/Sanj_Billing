<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseBillRequest;
use App\Models\BankAccount;
use App\Models\PaymentMethod;
use App\Models\PurchaseBill;
use App\Models\PurchaseOrder;
use App\Models\TdsSection;
use App\Services\PurchaseBillService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class PurchaseBillController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private PurchaseBillService $purchaseBillService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $bills = PurchaseBill::where('company_id', $companyId)
            ->with(['party', 'purchaseOrder', 'payments'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('bill_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('purchase-bills.index', [
            'bills' => $bills,
            'statuses' => config('inventory.purchase_bill_statuses'),
        ]);
    }

    public function create(Request $request)
    {
        $company = current_company_or_fail();
        $purchaseOrder = null;

        if ($request->filled('purchase_order_id')) {
            $purchaseOrder = PurchaseOrder::where('company_id', $company->id)
                ->where('status', 'Submitted')
                ->findOrFail($request->integer('purchase_order_id'));

            if ($purchaseOrder->purchaseBill()->exists()) {
                return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', 'This purchase order has already been billed.');
            }

            $purchaseOrder->load(['items.product', 'vendor']);
        }

        return view('purchase-bills.form', [
            'bill' => null,
            'purchaseOrder' => $purchaseOrder,
            'eligiblePurchaseOrders' => $purchaseOrder ? collect() : PurchaseOrder::where('company_id', $company->id)
                ->where('status', 'Submitted')
                ->whereDoesntHave('purchaseBill')
                ->with('vendor')
                ->orderByDesc('po_date')
                ->get(),
            'tdsSections' => TdsSection::where('company_id', $company->id)->where('status', 'active')->orderBy('section')->get(),
        ]);
    }

    public function store(PurchaseBillRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('purchase-bills.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $purchaseOrderId = $request->integer('purchase_order_id');
        $purchaseOrder = PurchaseOrder::where('company_id', $company->id)->findOrFail($purchaseOrderId);

        try {
            $bill = $this->purchaseBillService->createFromPurchaseOrder($purchaseOrder, $request->validated(), $company, $financialYear, Auth::user());
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-bills.create', ['purchase_order_id' => $purchaseOrderId])->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-bills.show', $bill)->with('status', "Purchase Bill {$bill->bill_number} saved as draft.");
    }

    public function show(PurchaseBill $purchaseBill)
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);

        $purchaseBill->load([
            'party', 'goodsReceipt.purchaseOrder', 'purchaseOrder', 'items.goodsReceiptItem.purchaseOrderItem.product', 'items.purchaseOrderItem.product',
            'tdsSection', 'creator', 'payments' => fn ($query) => $query->orderByDesc('payment_date'),
        ]);

        return view('purchase-bills.show', [
            'bill' => $purchaseBill,
            'bankAccounts' => BankAccount::where('company_id', $purchaseBill->company_id)->where('status', 'active')->orderBy('account_name')->get(),
            'paymentMethods' => PaymentMethod::where('company_id', $purchaseBill->company_id)->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function edit(PurchaseBill $purchaseBill)
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);

        if ($purchaseBill->status !== 'Draft') {
            return redirect()->route('purchase-bills.show', $purchaseBill)->with('error', 'Only a draft purchase bill can be edited.');
        }

        $company = current_company_or_fail();
        $purchaseBill->load(['purchaseOrder.vendor', 'items.purchaseOrderItem.product']);

        return view('purchase-bills.form', [
            'bill' => $purchaseBill,
            'purchaseOrder' => $purchaseBill->purchaseOrder,
            'eligiblePurchaseOrders' => collect(),
            'tdsSections' => TdsSection::where('company_id', $company->id)->where('status', 'active')->orderBy('section')->get(),
        ]);
    }

    public function update(PurchaseBillRequest $request, PurchaseBill $purchaseBill): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);

        try {
            $this->purchaseBillService->update($purchaseBill, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-bills.edit', $purchaseBill)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-bills.show', $purchaseBill)->with('status', 'Purchase bill updated.');
    }

    public function post(PurchaseBill $purchaseBill): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);

        try {
            $this->purchaseBillService->post($purchaseBill);
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-bills.show', $purchaseBill)->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-bills.show', $purchaseBill)->with('status', 'Purchase bill posted.');
    }

    public function cancel(PurchaseBill $purchaseBill): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);

        try {
            $this->purchaseBillService->cancel($purchaseBill);
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-bills.show', $purchaseBill)->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-bills.show', $purchaseBill)->with('status', 'Purchase bill cancelled.');
    }

    public function print(PurchaseBill $purchaseBill)
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);

        $purchaseBill->load(['party', 'company', 'items.goodsReceiptItem.purchaseOrderItem.product', 'items.purchaseOrderItem.product', 'tdsSection']);

        return Pdf::loadView('purchase-bills.print', ['bill' => $purchaseBill])->stream("{$purchaseBill->bill_number}.pdf");
    }
}

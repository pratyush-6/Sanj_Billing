<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleInvoiceRequest;
use App\Models\BankAccount;
use App\Models\DeliveryChallan;
use App\Models\Party;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\TdsSection;
use App\Services\SaleInvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SaleInvoiceController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private SaleInvoiceService $saleInvoiceService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $invoices = scope_to_branch(SaleInvoice::where('company_id', $companyId))
            ->with(['party', 'payments'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('sale-invoices.index', [
            'invoices' => $invoices,
            'statuses' => config('inventory.sale_invoice_statuses'),
        ]);
    }

    public function create(Request $request)
    {
        $company = current_company_or_fail();
        $challan = null;
        $challanLines = collect();
        $prefill = null;

        if ($request->filled('prefill_from')) {
            $source = SaleInvoice::where('company_id', $company->id)
                ->where('status', 'Cancelled')
                ->with('items.product')
                ->find($request->integer('prefill_from'));

            if ($source) {
                $prefill = [
                    'party_id' => $source->party_id,
                    'source_invoice_number' => $source->invoice_number,
                    'items' => $source->items->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                    ])->all(),
                ];
            }
        }

        if ($request->filled('from_challan')) {
            $challan = DeliveryChallan::where('company_id', $company->id)
                ->whereIn('status', ['Completed', 'Partially Invoiced'])
                ->findOrFail($request->integer('from_challan'));

            $challan->load(['party', 'items.product']);

            $invoicedByItem = SaleInvoiceItem::whereIn('delivery_challan_item_id', $challan->items->pluck('id'))
                ->whereHas('saleInvoice', fn ($query) => $query->where('status', '!=', 'Cancelled'))
                ->selectRaw('delivery_challan_item_id, SUM(quantity) as total_invoiced')
                ->groupBy('delivery_challan_item_id')
                ->pluck('total_invoiced', 'delivery_challan_item_id');

            $challanLines = $challan->items->map(function ($item) use ($invoicedByItem) {
                $remaining = round((float) $item->quantity - (float) ($invoicedByItem[$item->id] ?? 0), 2);

                return [
                    'delivery_challan_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_label' => $item->product->displayLabel(),
                    'remaining' => $remaining,
                ];
            })->filter(fn ($line) => $line['remaining'] > 0)->values();

            if ($challanLines->isEmpty()) {
                return redirect()->route('delivery-challans.show', $challan)->with('error', 'This delivery challan has already been fully invoiced.');
            }
        }

        return $this->formData(null, $challan, $challanLines, $prefill);
    }

    public function store(SaleInvoiceRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('sale-invoices.create')->with('error', 'No active financial year. Please set one up first.');
        }

        try {
            $invoice = $this->saleInvoiceService->create($request->validated(), $company, current_branch_or_fail(), $financialYear, Auth::user());
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-invoices.create')->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-invoices.show', $invoice)->with('status', "Sale Invoice {$invoice->invoice_number} saved as draft.");
    }

    public function show(SaleInvoice $saleInvoice)
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        $saleInvoice->load([
            'party', 'items.product', 'items.deliveryChallanItem.deliveryChallan',
            'tdsSection', 'creator', 'payments' => fn ($query) => $query->orderByDesc('payment_date'),
        ]);

        return view('sale-invoices.show', [
            'invoice' => $saleInvoice,
            'bankAccounts' => BankAccount::where('company_id', $saleInvoice->company_id)->where('status', 'active')->orderBy('account_name')->get(),
            'paymentMethods' => PaymentMethod::where('company_id', $saleInvoice->company_id)->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function edit(SaleInvoice $saleInvoice)
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        if ($saleInvoice->status !== 'Draft') {
            return redirect()->route('sale-invoices.show', $saleInvoice)->with('error', 'Only a draft sale invoice can be edited.');
        }

        $saleInvoice->load('items.product', 'items.deliveryChallanItem');

        return $this->formData($saleInvoice, null, collect());
    }

    public function update(SaleInvoiceRequest $request, SaleInvoice $saleInvoice): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        try {
            $this->saleInvoiceService->update($saleInvoice, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-invoices.edit', $saleInvoice)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-invoices.show', $saleInvoice)->with('status', 'Sale invoice updated.');
    }

    public function post(SaleInvoice $saleInvoice): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        try {
            $this->saleInvoiceService->post($saleInvoice);
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-invoices.show', $saleInvoice)->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-invoices.show', $saleInvoice)->with('status', 'Sale invoice posted.');
    }

    public function cancel(SaleInvoice $saleInvoice): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        try {
            $this->saleInvoiceService->cancel($saleInvoice);
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-invoices.show', $saleInvoice)->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-invoices.show', $saleInvoice)->with('status', 'Sale invoice cancelled.');
    }

    public function print(SaleInvoice $saleInvoice)
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        $saleInvoice->load(['party', 'company', 'items.product', 'tdsSection']);

        return Pdf::loadView('sale-invoices.print', ['invoice' => $saleInvoice])->stream("{$saleInvoice->invoice_number}.pdf");
    }

    /**
     * "Editing" a quantity is cancel-and-recreate, not an in-place mutation —
     * cancels the invoice (reversing its stock/accounting via the existing
     * cancel() path) and hands back a fresh create form pre-filled from it,
     * so the user only has to adjust the quantity and resubmit. A from-challan
     * invoice's correction becomes a direct sale on the new invoice — its
     * challan-eligibility isn't re-derived on a copy.
     */
    public function copy(SaleInvoice $saleInvoice): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        try {
            $this->saleInvoiceService->cancel($saleInvoice);
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-invoices.show', $saleInvoice)->with('error', $exception->getMessage());
        }

        $saleInvoice->load('items.product');

        return redirect()->route('sale-invoices.create', ['prefill_from' => $saleInvoice->id]);
    }

    private function formData(?SaleInvoice $invoice = null, ?DeliveryChallan $challan = null, $challanLines = null, ?array $prefill = null)
    {
        $company = current_company_or_fail();

        return view('sale-invoices.form', [
            'invoice' => $invoice,
            'challan' => $challan,
            'challanLines' => $challanLines ?? collect(),
            'prefill' => $prefill,
            'parties' => Party::where('company_id', $company->id)->where('is_customer', true)->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::where('company_id', $company->id)->where('status', 'active')->with(['unit', 'secondaryUnit'])->orderBy('name')->get(),
            'tdsSections' => TdsSection::where('company_id', $company->id)->where('status', 'active')->orderBy('section')->get(),
        ]);
    }
}

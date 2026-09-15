<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\GoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class GoodsReceiptController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private GoodsReceiptService $goodsReceiptService) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $goodsReceipts = GoodsReceipt::where('company_id', $companyId)
            ->with(['purchaseOrder.vendor', 'creator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('receipt_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('goods-receipts.index', [
            'goodsReceipts' => $goodsReceipts,
            'statuses' => config('inventory.goods_receipt_statuses'),
        ]);
    }

    public function create(Request $request)
    {
        $company = current_company_or_fail();
        $purchaseOrder = null;

        if ($request->filled('purchase_order_id')) {
            $purchaseOrder = PurchaseOrder::where('company_id', $company->id)
                ->whereIn('status', ['Sent', 'Partially Received'])
                ->with('items.product')
                ->findOrFail($request->integer('purchase_order_id'));
        }

        return view('goods-receipts.form', [
            'goodsReceipt' => null,
            'purchaseOrder' => $purchaseOrder,
            'eligiblePurchaseOrders' => PurchaseOrder::where('company_id', $company->id)
                ->whereIn('status', ['Sent', 'Partially Received'])
                ->with('vendor')
                ->orderByDesc('po_date')
                ->get(),
            'remainingByItem' => $purchaseOrder ? $this->remainingByItem($purchaseOrder) : [],
        ]);
    }

    public function store(GoodsReceiptRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('goods-receipts.create')->with('error', 'No active financial year. Please set one up first.');
        }

        try {
            $goodsReceipt = $this->goodsReceiptService->create($request->validated(), $company, $financialYear, Auth::user());
        } catch (RuntimeException $exception) {
            return redirect()->route('goods-receipts.create', ['purchase_order_id' => $request->input('purchase_order_id')])
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('goods-receipts.show', $goodsReceipt)->with('status', "Goods Receipt {$goodsReceipt->grn_number} saved as draft.");
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->ensureBelongsToCurrentCompany($goodsReceipt);

        $goodsReceipt->load(['purchaseOrder.vendor', 'creator', 'items.purchaseOrderItem.product']);

        return view('goods-receipts.show', ['goodsReceipt' => $goodsReceipt]);
    }

    public function edit(GoodsReceipt $goodsReceipt)
    {
        $this->ensureBelongsToCurrentCompany($goodsReceipt);

        if ($goodsReceipt->status !== 'Draft') {
            return redirect()->route('goods-receipts.show', $goodsReceipt)->with('error', 'Only draft goods receipts can be edited.');
        }

        $goodsReceipt->load(['purchaseOrder.items.product', 'items']);

        return view('goods-receipts.form', [
            'goodsReceipt' => $goodsReceipt,
            'purchaseOrder' => $goodsReceipt->purchaseOrder,
            'eligiblePurchaseOrders' => collect(),
            'remainingByItem' => $this->remainingByItem($goodsReceipt->purchaseOrder),
        ]);
    }

    public function update(GoodsReceiptRequest $request, GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($goodsReceipt);

        try {
            $this->goodsReceiptService->update($goodsReceipt, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('goods-receipts.edit', $goodsReceipt)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('goods-receipts.show', $goodsReceipt)->with('status', 'Goods receipt updated.');
    }

    public function complete(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($goodsReceipt);

        try {
            $this->goodsReceiptService->complete($goodsReceipt, Auth::user());
        } catch (RuntimeException $exception) {
            return redirect()->route('goods-receipts.show', $goodsReceipt)->with('error', $exception->getMessage());
        }

        return redirect()->route('goods-receipts.show', $goodsReceipt)->with('status', 'Goods receipt completed and stock updated.');
    }

    private function remainingByItem(PurchaseOrder $purchaseOrder): array
    {
        $receivedByItem = GoodsReceiptItem::whereIn('purchase_order_item_id', $purchaseOrder->items->pluck('id'))
            ->whereHas('goodsReceipt', fn ($query) => $query->where('status', 'Completed'))
            ->selectRaw('purchase_order_item_id, SUM(quantity_received) as total_received')
            ->groupBy('purchase_order_item_id')
            ->pluck('total_received', 'purchase_order_item_id');

        $remaining = [];

        foreach ($purchaseOrder->items as $item) {
            $remaining[$item->id] = round((float) $item->quantity - (float) ($receivedByItem[$item->id] ?? 0), 2);
        }

        return $remaining;
    }
}

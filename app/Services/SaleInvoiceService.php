<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanItem;
use App\Models\FinancialYear;
use App\Models\Party;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\StockMovement;
use App\Models\TdsSection;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleInvoiceService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private StockMovementService $stockMovementService,
        private GstCalculationService $gstCalculator,
        private AccountingService $accountingService,
    ) {}

    /**
     * All-or-nothing per invoice: either every line carries a delivery_challan_item_id
     * (stock already moved when that challan was completed — never posted again here),
     * or none do (a direct sale, where this is the only document, so it posts the
     * stock-out itself). Mixing the two within one invoice is rejected outright,
     * matching scope decision 4.
     */
    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): SaleInvoice
    {
        $items = $data['items'];
        $linkedCount = collect($items)->filter(fn ($item) => ! empty($item['delivery_challan_item_id']))->count();

        if ($linkedCount > 0 && $linkedCount !== count($items)) {
            throw new RuntimeException('An invoice must either be created entirely from delivery challan lines, or entirely as a direct sale — not a mix of both.');
        }

        $isFromChallan = $linkedCount > 0;

        return DB::transaction(function () use ($data, $items, $isFromChallan, $company, $financialYear, $creator) {
            $party = Party::where('company_id', $company->id)->where('is_customer', true)->findOrFail($data['party_id']);

            $challanItems = collect();
            if ($isFromChallan) {
                $challanItems = $this->validateChallanLines($items, $company, $party);
            }

            $lines = $this->buildLines($items, $company, $party);
            $totals = $this->sumTotals($lines);

            $tdsAmount = 0.0;
            if (! empty($data['tds_section_id'])) {
                $tdsSection = TdsSection::where('company_id', $company->id)->findOrFail($data['tds_section_id']);
                $tdsAmount = round($totals['total_amount'] * (float) $tdsSection->rate / 100, 2);
            }

            $invoice = SaleInvoice::create([
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'party_id' => $party->id,
                'invoice_number' => $this->generateSequentialNumber(SaleInvoice::class, 'INV', $company, $financialYear),
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'Draft',
                'taxable_amount' => $totals['taxable_amount'],
                'cgst_amount' => $totals['cgst_amount'],
                'sgst_amount' => $totals['sgst_amount'],
                'igst_amount' => $totals['igst_amount'],
                'tds_section_id' => $data['tds_section_id'] ?? null,
                'tds_amount' => $tdsAmount,
                'total_amount' => $totals['total_amount'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator->id,
            ]);

            foreach ($lines as $line) {
                if ($isFromChallan) {
                    $challanItem = $challanItems->get($line['delivery_challan_item_id']);
                    $movement = StockMovement::where('source_type', DeliveryChallanItem::class)
                        ->where('source_id', $challanItem->id)
                        ->first();
                    $line['unit_cost'] = $movement?->unit_cost;
                    $line['total_cost'] = $movement?->total_cost;
                } else {
                    $movement = $this->stockMovementService->postOut(
                        $company,
                        $financialYear,
                        Product::find($line['product_id']),
                        (float) $line['quantity'],
                        $data['invoice_date'],
                        $invoice,
                        $invoice->invoice_number,
                        null,
                        $creator,
                    );
                    $line['unit_cost'] = $movement->unit_cost;
                    $line['total_cost'] = $movement->total_cost;
                }

                $invoice->items()->create($line);
            }

            if ($isFromChallan) {
                $challanItems->pluck('delivery_challan_id')->unique()->each(
                    fn ($challanId) => $this->refreshDeliveryChallanStatus(DeliveryChallan::findOrFail($challanId))
                );
            }

            $this->auditLog->log('Sale Invoice Created', 'Sale Invoice', $invoice, null, $invoice->toArray());

            return $invoice;
        });
    }

    public function update(SaleInvoice $invoice, array $data): SaleInvoice
    {
        if ($invoice->status !== 'Draft') {
            throw new RuntimeException('Only a draft sale invoice can be edited.');
        }

        return DB::transaction(function () use ($invoice, $data) {
            $old = $invoice->toArray();

            $tdsAmount = 0.0;
            if (! empty($data['tds_section_id'])) {
                $tdsSection = TdsSection::findOrFail($data['tds_section_id']);
                $tdsAmount = round((float) $invoice->total_amount * (float) $tdsSection->rate / 100, 2);
            }

            $invoice->update([
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'tds_section_id' => $data['tds_section_id'] ?? null,
                'tds_amount' => $tdsAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditLog->log('Sale Invoice Updated', 'Sale Invoice', $invoice, $old, $invoice->toArray());

            return $invoice;
        });
    }

    public function post(SaleInvoice $invoice): SaleInvoice
    {
        if ($invoice->status !== 'Draft') {
            throw new RuntimeException('Only a draft sale invoice can be posted.');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'Posted']);
            $invoice->load('company', 'financialYear', 'items');

            $this->accountingService->recordSaleInvoice($invoice);

            $this->auditLog->log('Sale Invoice Posted', 'Sale Invoice', $invoice, null, ['status' => 'Posted']);

            return $invoice;
        });
    }

    public function cancel(SaleInvoice $invoice): SaleInvoice
    {
        if ($invoice->status === 'Cancelled') {
            throw new RuntimeException('This sale invoice is already cancelled.');
        }

        if ($invoice->status === 'Posted' && $invoice->amountPaid() > 0) {
            throw new RuntimeException('This sale invoice has payments recorded against it — reverse those payments before cancelling.');
        }

        return DB::transaction(function () use ($invoice) {
            if ($invoice->status === 'Posted') {
                $this->accountingService->voidSaleInvoice($invoice);
            }

            $invoice->update(['status' => 'Cancelled']);

            $invoice->load('items.deliveryChallanItem');
            $invoice->items->pluck('deliveryChallanItem.delivery_challan_id')->filter()->unique()->each(
                fn ($challanId) => $this->refreshDeliveryChallanStatus(DeliveryChallan::findOrFail($challanId))
            );

            $this->auditLog->log('Sale Invoice Cancelled', 'Sale Invoice', $invoice, null, ['status' => 'Cancelled']);

            return $invoice;
        });
    }

    /**
     * Guards against over-invoicing by comparing against quantity already invoiced
     * across every *non-cancelled* invoice for this challan line — never a stored
     * running total, same discipline as GoodsReceiptService::syncItems().
     */
    private function validateChallanLines(array $items, Company $company, Party $party)
    {
        $challanItemIds = collect($items)->pluck('delivery_challan_item_id')->filter()->unique();

        $challanItems = DeliveryChallanItem::whereIn('id', $challanItemIds)
            ->with('deliveryChallan')
            ->get()
            ->keyBy('id');

        if ($challanItems->count() !== $challanItemIds->count()) {
            throw new RuntimeException('One of the selected delivery challan lines could not be found.');
        }

        $invoicedByItem = SaleInvoiceItem::whereIn('delivery_challan_item_id', $challanItemIds)
            ->whereHas('saleInvoice', fn ($query) => $query->where('status', '!=', 'Cancelled'))
            ->selectRaw('delivery_challan_item_id, SUM(quantity) as total_invoiced')
            ->groupBy('delivery_challan_item_id')
            ->pluck('total_invoiced', 'delivery_challan_item_id');

        foreach ($items as $item) {
            $challanItem = $challanItems->get($item['delivery_challan_item_id']);
            $challan = $challanItem->deliveryChallan;

            if ($challan->company_id !== $company->id) {
                throw new RuntimeException('One of the selected delivery challan lines does not belong to this company.');
            }

            if ($challan->party_id !== $party->id) {
                throw new RuntimeException('All delivery challan lines on this invoice must belong to the invoice\'s customer.');
            }

            if (! in_array($challan->status, ['Completed', 'Partially Invoiced'], true)) {
                throw new RuntimeException("Delivery challan {$challan->challan_number} is not eligible for invoicing.");
            }

            $alreadyInvoiced = (float) ($invoicedByItem[$challanItem->id] ?? 0);
            $remaining = round((float) $challanItem->quantity - $alreadyInvoiced, 2);
            $requested = round((float) $item['quantity'], 2);

            if ($requested > $remaining + 0.01) {
                throw new RuntimeException("Cannot invoice more than the remaining quantity for {$challanItem->product->name} ({$remaining} left).");
            }
        }

        return $challanItems;
    }

    private function buildLines(array $items, Company $company, Party $party): array
    {
        return collect($items)->map(function ($item) use ($company, $party) {
            $product = Product::where('company_id', $company->id)->with('gstRate')->findOrFail($item['product_id']);
            $quantity = round((float) $item['quantity'], 2);
            $unitPrice = round((float) $item['unit_price'], 2);
            $taxableAmount = round($quantity * $unitPrice, 2);
            $gstRate = (float) ($product->gstRate?->rate ?? 0);

            $gstSplit = $this->gstCalculator->splitGst($taxableAmount, $gstRate, $company->state, $party->state);

            return [
                'delivery_challan_item_id' => $item['delivery_challan_item_id'] ?? null,
                'product_id' => $product->id,
                'hsn_code' => $product->hsn_code,
                'gst_rate' => $gstRate,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'taxable_amount' => $taxableAmount,
                'cgst_amount' => $gstSplit['cgst_amount'],
                'sgst_amount' => $gstSplit['sgst_amount'],
                'igst_amount' => $gstSplit['igst_amount'],
                'amount' => round($taxableAmount + $gstSplit['cgst_amount'] + $gstSplit['sgst_amount'] + $gstSplit['igst_amount'], 2),
            ];
        })->all();
    }

    private function sumTotals(array $lines): array
    {
        $taxableAmount = round(array_sum(array_column($lines, 'taxable_amount')), 2);
        $cgstAmount = round(array_sum(array_column($lines, 'cgst_amount')), 2);
        $sgstAmount = round(array_sum(array_column($lines, 'sgst_amount')), 2);
        $igstAmount = round(array_sum(array_column($lines, 'igst_amount')), 2);

        return [
            'taxable_amount' => $taxableAmount,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'igst_amount' => $igstAmount,
            'total_amount' => round($taxableAmount + $cgstAmount + $sgstAmount + $igstAmount, 2),
        ];
    }

    private function refreshDeliveryChallanStatus(DeliveryChallan $challan): void
    {
        $challan->load('items');

        $invoicedByItem = SaleInvoiceItem::whereIn('delivery_challan_item_id', $challan->items->pluck('id'))
            ->whereHas('saleInvoice', fn ($query) => $query->where('status', '!=', 'Cancelled'))
            ->selectRaw('delivery_challan_item_id, SUM(quantity) as total_invoiced')
            ->groupBy('delivery_challan_item_id')
            ->pluck('total_invoiced', 'delivery_challan_item_id');

        $fullyInvoiced = true;
        $anyInvoiced = false;

        foreach ($challan->items as $item) {
            $invoiced = (float) ($invoicedByItem[$item->id] ?? 0);

            if ($invoiced > 0) {
                $anyInvoiced = true;
            }

            if ($invoiced < (float) $item->quantity) {
                $fullyInvoiced = false;
            }
        }

        $status = $fullyInvoiced ? 'Invoiced' : ($anyInvoiced ? 'Partially Invoiced' : 'Completed');

        if ($status !== $challan->status) {
            $challan->update(['status' => $status]);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\GoodsReceipt;
use App\Models\Party;
use App\Models\PurchaseBill;
use App\Models\TdsSection;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseBillService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private AccountingService $accountingService,
        private GstCalculationService $gstCalculator,
    ) {}

    public function createFromGoodsReceipt(GoodsReceipt $goodsReceipt, array $data, Company $company, FinancialYear $financialYear, User $creator): PurchaseBill
    {
        if ($goodsReceipt->status !== 'Completed') {
            throw new RuntimeException('Only a completed goods receipt can be billed.');
        }

        if ($goodsReceipt->purchaseBill()->exists()) {
            throw new RuntimeException('This goods receipt has already been billed.');
        }

        return DB::transaction(function () use ($goodsReceipt, $data, $company, $financialYear, $creator) {
            $goodsReceipt->load(['items.purchaseOrderItem.product.gstRate', 'purchaseOrder.vendor']);
            $party = $goodsReceipt->purchaseOrder->vendor;

            $lines = $this->buildLines($goodsReceipt, $company, $party);
            $totals = $this->sumTotals($lines);

            $tdsAmount = 0.0;
            if (! empty($data['tds_section_id'])) {
                $tdsSection = TdsSection::findOrFail($data['tds_section_id']);
                $tdsAmount = round($totals['total_amount'] * (float) $tdsSection->rate / 100, 2);
            }

            $bill = PurchaseBill::create([
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'goods_receipt_id' => $goodsReceipt->id,
                'party_id' => $party->id,
                'bill_number' => $this->generateSequentialNumber(PurchaseBill::class, 'BILL', $company, $financialYear),
                'bill_date' => $data['bill_date'],
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
                $bill->items()->create($line);
            }

            $this->auditLog->log('Purchase Bill Created', 'Purchase Bill', $bill, null, $bill->toArray());

            return $bill;
        });
    }

    public function update(PurchaseBill $bill, array $data): PurchaseBill
    {
        if ($bill->status !== 'Draft') {
            throw new RuntimeException('Only a draft purchase bill can be edited.');
        }

        return DB::transaction(function () use ($bill, $data) {
            $old = $bill->toArray();

            $tdsAmount = 0.0;
            if (! empty($data['tds_section_id'])) {
                $tdsSection = TdsSection::findOrFail($data['tds_section_id']);
                $tdsAmount = round((float) $bill->total_amount * (float) $tdsSection->rate / 100, 2);
            }

            $bill->update([
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'tds_section_id' => $data['tds_section_id'] ?? null,
                'tds_amount' => $tdsAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditLog->log('Purchase Bill Updated', 'Purchase Bill', $bill, $old, $bill->toArray());

            return $bill;
        });
    }

    public function post(PurchaseBill $bill): PurchaseBill
    {
        if ($bill->status !== 'Draft') {
            throw new RuntimeException('Only a draft purchase bill can be posted.');
        }

        return DB::transaction(function () use ($bill) {
            $bill->update(['status' => 'Posted']);
            $bill->load('company', 'financialYear');

            $this->accountingService->recordPurchaseBill($bill);

            $this->auditLog->log('Purchase Bill Posted', 'Purchase Bill', $bill, null, ['status' => 'Posted']);

            return $bill;
        });
    }

    public function cancel(PurchaseBill $bill): PurchaseBill
    {
        if ($bill->status === 'Cancelled') {
            throw new RuntimeException('This purchase bill is already cancelled.');
        }

        if ($bill->amountPaid() > 0) {
            throw new RuntimeException('This purchase bill has payments recorded against it — reverse those payments before cancelling.');
        }

        return DB::transaction(function () use ($bill) {
            if ($bill->status === 'Posted') {
                $this->accountingService->voidPurchaseBill($bill);
            }

            $bill->update(['status' => 'Cancelled']);

            $this->auditLog->log('Purchase Bill Cancelled', 'Purchase Bill', $bill, null, ['status' => 'Cancelled']);

            return $bill;
        });
    }

    private function buildLines(GoodsReceipt $goodsReceipt, Company $company, Party $party): array
    {
        return $goodsReceipt->items->map(function ($item) use ($company, $party) {
            $product = $item->purchaseOrderItem->product;
            $quantity = round((float) $item->quantity_received, 2);
            $unitPrice = round((float) $item->purchaseOrderItem->unit_price, 2);
            $taxableAmount = round($quantity * $unitPrice, 2);
            $gstRate = (float) ($product->gstRate?->rate ?? 0);

            $gstSplit = $this->gstCalculator->splitGst($taxableAmount, $gstRate, $company->state, $party->state);

            return [
                'goods_receipt_item_id' => $item->id,
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
}

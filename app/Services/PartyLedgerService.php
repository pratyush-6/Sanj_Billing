<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Party;
use App\Models\Payment;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use Illuminate\Support\Collection;

class PartyLedgerService
{
    /**
     * One grouped query per source table for however many parties are asked for,
     * mirrors StockLevelService::currentStock()/AccountingService::computeBalances()
     * exactly — never N+1 per party row. "balance" is signed from the business's
     * point of view: positive means the party owes the business, negative means
     * the business owes the party — coherent even for a party that is both a
     * vendor and a customer, since receivable and payable net against each other.
     * TDS is excluded from both sides (never collected/paid via a Payment),
     * matching SaleInvoice::amountDue()/PurchaseBill::amountDue() exactly.
     */
    public function outstandingBalances(Company $company): Collection
    {
        $parties = Party::where('company_id', $company->id)->orderBy('name')->get();

        if ($parties->isEmpty()) {
            return collect();
        }

        $partyIds = $parties->pluck('id');

        $receivable = SaleInvoice::whereIn('party_id', $partyIds)
            ->where('status', 'Posted')
            ->selectRaw('party_id, SUM(total_amount - tds_amount) as total')
            ->groupBy('party_id')
            ->pluck('total', 'party_id');

        $payable = PurchaseBill::whereIn('party_id', $partyIds)
            ->where('status', 'Posted')
            ->selectRaw('party_id, SUM(total_amount - tds_amount) as total')
            ->groupBy('party_id')
            ->pluck('total', 'party_id');

        $paymentsIn = Payment::whereIn('party_id', $partyIds)
            ->where('status', 'Posted')->where('direction', 'In')
            ->selectRaw('party_id, SUM(amount) as total')
            ->groupBy('party_id')
            ->pluck('total', 'party_id');

        $paymentsOut = Payment::whereIn('party_id', $partyIds)
            ->where('status', 'Posted')->where('direction', 'Out')
            ->selectRaw('party_id, SUM(amount) as total')
            ->groupBy('party_id')
            ->pluck('total', 'party_id');

        return $parties->map(function (Party $party) use ($receivable, $payable, $paymentsIn, $paymentsOut) {
            $rec = round((float) ($receivable[$party->id] ?? 0) - (float) ($paymentsIn[$party->id] ?? 0), 2);
            $pay = round((float) ($payable[$party->id] ?? 0) - (float) ($paymentsOut[$party->id] ?? 0), 2);

            return [
                'party' => $party,
                'receivable' => $rec,
                'payable' => $pay,
                'balance' => round($rec - $pay, 2),
            ];
        });
    }

    /**
     * Every Sale Invoice / Purchase Bill / Payment for this party, chronological,
     * with a running balance derived on the fly — never stored, same philosophy
     * as AccountingService::ledger(), just sourced from these three tables.
     */
    public function statementFor(Party $party): Collection
    {
        $rows = collect();

        foreach (SaleInvoice::where('party_id', $party->id)->where('status', 'Posted')->get() as $invoice) {
            $rows->push([
                'date' => $invoice->invoice_date,
                'type' => 'Sale Invoice',
                'reference' => $invoice->invoice_number,
                'route' => route('sale-invoices.show', $invoice),
                'amount' => round((float) $invoice->total_amount - (float) $invoice->tds_amount, 2),
            ]);
        }

        foreach (PurchaseBill::where('party_id', $party->id)->where('status', 'Posted')->get() as $bill) {
            $rows->push([
                'date' => $bill->bill_date,
                'type' => 'Purchase Bill',
                'reference' => $bill->bill_number,
                'route' => route('purchase-bills.show', $bill),
                'amount' => -round((float) $bill->total_amount - (float) $bill->tds_amount, 2),
            ]);
        }

        foreach (Payment::where('party_id', $party->id)->where('status', 'Posted')->get() as $payment) {
            $rows->push([
                'date' => $payment->payment_date,
                'type' => $payment->direction === 'In' ? 'Payment Received' : 'Payment Paid',
                'reference' => $payment->payment_number,
                'route' => null,
                'amount' => $payment->direction === 'In' ? -round((float) $payment->amount, 2) : round((float) $payment->amount, 2),
            ]);
        }

        $running = 0.0;

        return $rows->sortBy('date')->values()->map(function ($row) use (&$running) {
            $running = round($running + $row['amount'], 2);
            $row['running_balance'] = $running;

            return $row;
        });
    }

    /**
     * Per-party-per-product transaction history: every Sale Invoice / Purchase
     * Bill line for this party, chronological — the view groups these by product.
     */
    public function itemWiseHistory(Party $party): Collection
    {
        $rows = collect();

        $saleItems = SaleInvoiceItem::whereHas('saleInvoice', fn ($query) => $query->where('party_id', $party->id)->where('status', 'Posted'))
            ->with('saleInvoice', 'product')
            ->get();

        foreach ($saleItems as $item) {
            $rows->push([
                'date' => $item->saleInvoice->invoice_date,
                'type' => 'Sale',
                'reference' => $item->saleInvoice->invoice_number,
                'route' => route('sale-invoices.show', $item->saleInvoice),
                'product' => $item->product,
                'direction' => 'Out',
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ]);
        }

        $billItems = PurchaseBillItem::whereHas('purchaseBill', fn ($query) => $query->where('party_id', $party->id)->where('status', 'Posted'))
            ->with('purchaseBill', 'goodsReceiptItem.purchaseOrderItem.product')
            ->get();

        foreach ($billItems as $item) {
            $rows->push([
                'date' => $item->purchaseBill->bill_date,
                'type' => 'Purchase',
                'reference' => $item->purchaseBill->bill_number,
                'route' => route('purchase-bills.show', $item->purchaseBill),
                'product' => $item->goodsReceiptItem->purchaseOrderItem->product,
                'direction' => 'In',
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ]);
        }

        return $rows->sortBy('date')->values()->groupBy(fn ($row) => $row['product']->id);
    }
}

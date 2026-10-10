<?php

namespace App\Services;

use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\SaleOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Consolidated sales reporting across every branch of a company. "Invoiced Sales"
 * (Posted Sale Invoices — the real, billed revenue, matching what already drives
 * AccountingService::recordSaleInvoice()) and "Order Pipeline" (Confirmed Sale
 * Orders not yet invoiced) are deliberately kept as two separate figures and never
 * summed: the schema has no direct link from a Sale Order to the invoice it
 * eventually becomes (it flows through a Delivery Challan first), so adding them
 * together would risk counting the same sale twice. Every invoice total aggregates
 * at the document-header level only (sale_invoices.total_amount is never re-derived
 * from a joined line-item table, so there is no multiplication risk there). The
 * order pipeline total is the one place that joins to line items — sale_orders has
 * no stored total of its own — and uses COUNT(DISTINCT sale_orders.id) alongside
 * SUM(sale_order_items.amount) so the item join inflates the sum correctly without
 * inflating the order count.
 */
class SalesReportService
{
    private function invoiceQuery(int $companyId, ?string $dateFrom, ?string $dateTo, ?int $branchId, ?int $partyId): Builder
    {
        return SaleInvoice::query()
            ->where('sale_invoices.company_id', $companyId)
            ->where('sale_invoices.status', 'Posted')
            ->when($dateFrom, fn ($q) => $q->whereDate('sale_invoices.invoice_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('sale_invoices.invoice_date', '<=', $dateTo))
            ->when($branchId, fn ($q) => $q->where('sale_invoices.branch_id', $branchId))
            ->when($partyId, fn ($q) => $q->where('sale_invoices.party_id', $partyId));
    }

    public function invoicedSummary(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $branchId = null, ?int $partyId = null): array
    {
        $row = $this->invoiceQuery($companyId, $dateFrom, $dateTo, $branchId, $partyId)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->first();

        return ['count' => (int) $row->count, 'total' => (float) $row->total];
    }

    public function invoicedByBranch(int $companyId, ?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        return $this->invoiceQuery($companyId, $dateFrom, $dateTo, null, null)
            ->leftJoin('branches', 'branches.id', '=', 'sale_invoices.branch_id')
            ->selectRaw("COALESCE(branches.id, 0) as branch_id, COALESCE(branches.name, 'Unassigned') as branch_name, SUM(sale_invoices.total_amount) as total, COUNT(*) as count")
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => (object) $row->getAttributes());
    }

    public function invoicedByParty(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $branchId = null): Collection
    {
        return $this->invoiceQuery($companyId, $dateFrom, $dateTo, $branchId, null)
            ->join('parties', 'parties.id', '=', 'sale_invoices.party_id')
            ->selectRaw('parties.id as party_id, parties.name as party_name, SUM(sale_invoices.total_amount) as total, COUNT(*) as count')
            ->groupBy('parties.id', 'parties.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => (object) $row->getAttributes());
    }

    public function invoicedTrend(int $companyId, int $months = 6, ?int $branchId = null): Collection
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $rows = $this->invoiceQuery($companyId, $start->toDateString(), now()->toDateString(), $branchId, null)
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, SUM(total_amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return collect(range(0, $months - 1))->map(function ($i) use ($start, $rows) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            return (object) ['label' => $month->format('M Y'), 'total' => (float) ($rows[$key] ?? 0)];
        });
    }

    /**
     * One row per sale line — product, quantity, branch and who recorded the sale
     * (the invoice's creator). This is the only place that reads line items, and it
     * never feeds into any of the totals above; it's for browsing, not aggregation.
     */
    public function invoiceLines(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $branchId = null, ?int $partyId = null, int $perPage = 30): LengthAwarePaginator
    {
        return SaleInvoiceItem::query()
            ->whereHas('saleInvoice', fn ($q) => $q
                ->where('company_id', $companyId)
                ->where('status', 'Posted')
                ->when($dateFrom, fn ($q) => $q->whereDate('invoice_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('invoice_date', '<=', $dateTo))
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($partyId, fn ($q) => $q->where('party_id', $partyId)))
            ->with(['product', 'saleInvoice.branch', 'saleInvoice.party', 'saleInvoice.creator'])
            ->join('sale_invoices', 'sale_invoices.id', '=', 'sale_invoice_items.sale_invoice_id')
            ->orderByDesc('sale_invoices.invoice_date')
            ->orderByDesc('sale_invoice_items.id')
            ->select('sale_invoice_items.*')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Confirmed orders not yet cancelled — shown as a separate, clearly-labelled
     * "not yet invoiced" figure. Never merged with invoicedSummary().
     */
    public function orderPipeline(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $branchId = null): array
    {
        $row = SaleOrder::query()
            ->where('sale_orders.company_id', $companyId)
            ->whereIn('sale_orders.status', ['Draft', 'Confirmed'])
            ->when($dateFrom, fn ($q) => $q->whereDate('sale_orders.order_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('sale_orders.order_date', '<=', $dateTo))
            ->when($branchId, fn ($q) => $q->where('sale_orders.branch_id', $branchId))
            ->join('sale_order_items', 'sale_order_items.sale_order_id', '=', 'sale_orders.id')
            ->selectRaw('COUNT(DISTINCT sale_orders.id) as count, COALESCE(SUM(sale_order_items.amount), 0) as total')
            ->first();

        return ['count' => (int) $row->count, 'total' => (float) $row->total];
    }
}

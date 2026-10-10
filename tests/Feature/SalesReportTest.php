<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Party;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\User;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private FinancialYear $financialYear;

    private Branch $branchA;

    private Branch $branchB;

    private Party $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Co', 'status' => 'active']);

        $this->financialYear = FinancialYear::create([
            'company_id' => $this->company->id,
            'name' => 'FY 2026-27',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_active' => true,
        ]);

        $this->branchA = Branch::create(['company_id' => $this->company->id, 'primary_company_id' => $this->company->id, 'name' => 'Alpha', 'code' => 'A', 'status' => 'active']);
        $this->branchB = Branch::create(['company_id' => $this->company->id, 'name' => 'Beta', 'code' => 'B', 'status' => 'active']);

        $this->customer = Party::create(['company_id' => $this->company->id, 'name' => 'Acme Retail', 'is_vendor' => false, 'is_customer' => true]);
    }

    private function invoice(Branch $branch, string $status, float $amount, string $date, ?User $creator = null): SaleInvoice
    {
        static $n = 0;
        $n++;

        return SaleInvoice::create([
            'company_id' => $this->company->id,
            'branch_id' => $branch->id,
            'financial_year_id' => $this->financialYear->id,
            'party_id' => $this->customer->id,
            'invoice_number' => "INV-TEST-{$n}",
            'invoice_date' => $date,
            'status' => $status,
            'total_amount' => $amount,
            'created_by' => $creator?->id,
        ]);
    }

    public function test_only_posted_invoices_count_toward_invoiced_sales(): void
    {
        $this->invoice($this->branchA, 'Posted', 1000, '2026-05-01');
        $this->invoice($this->branchA, 'Draft', 500, '2026-05-01');
        $this->invoice($this->branchA, 'Cancelled', 700, '2026-05-01');

        $summary = app(SalesReportService::class)->invoicedSummary($this->company->id);

        $this->assertSame(1, $summary['count']);
        $this->assertSame(1000.0, $summary['total']);
    }

    public function test_branch_wise_totals_sum_to_the_company_total(): void
    {
        $this->invoice($this->branchA, 'Posted', 1000, '2026-05-01');
        $this->invoice($this->branchB, 'Posted', 500, '2026-05-02');

        $service = app(SalesReportService::class);
        $summary = $service->invoicedSummary($this->company->id);
        $byBranch = $service->invoicedByBranch($this->company->id);

        $this->assertSame(1500.0, $summary['total']);
        $this->assertSame(1500.0, (float) $byBranch->sum('total'));
        $this->assertCount(2, $byBranch);
    }

    public function test_date_range_and_branch_filters_narrow_the_result(): void
    {
        $this->invoice($this->branchA, 'Posted', 1000, '2026-05-01');
        $this->invoice($this->branchA, 'Posted', 300, '2026-06-15');
        $this->invoice($this->branchB, 'Posted', 500, '2026-05-01');

        $service = app(SalesReportService::class);

        $mayOnly = $service->invoicedSummary($this->company->id, '2026-05-01', '2026-05-31');
        $this->assertSame(1500.0, $mayOnly['total']);

        $branchAOnly = $service->invoicedSummary($this->company->id, null, null, $this->branchA->id);
        $this->assertSame(1300.0, $branchAOnly['total']);
    }

    public function test_order_pipeline_is_reported_separately_and_never_added_to_invoiced_total(): void
    {
        $product = Product::create(['company_id' => $this->company->id, 'name' => 'Widget', 'status' => 'active']);

        $this->invoice($this->branchA, 'Posted', 1000, '2026-05-01');

        $order = SaleOrder::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branchA->id,
            'financial_year_id' => $this->financialYear->id,
            'party_id' => $this->customer->id,
            'order_number' => 'SO-TEST-1',
            'order_date' => '2026-05-01',
            'status' => 'Confirmed',
        ]);

        SaleOrderItem::create([
            'sale_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 250,
            'amount' => 500,
        ]);

        $service = app(SalesReportService::class);
        $summary = $service->invoicedSummary($this->company->id);
        $pipeline = $service->orderPipeline($this->company->id);

        $this->assertSame(1000.0, $summary['total']);
        $this->assertSame(500.0, $pipeline['total']);
        $this->assertSame(1, $pipeline['count']);
    }

    public function test_invoice_lines_show_product_branch_and_who_sold_it(): void
    {
        $salesperson = User::factory()->create(['name' => 'Priya Sharma']);
        $product = Product::create(['company_id' => $this->company->id, 'name' => 'Widget', 'status' => 'active']);

        $invoice = $this->invoice($this->branchA, 'Posted', 500, '2026-05-01', $salesperson);
        SaleInvoiceItem::create([
            'sale_invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'gst_rate' => 0,
            'quantity' => 5,
            'unit_price' => 100,
            'amount' => 500,
        ]);

        // A draft invoice's lines must not appear
        $draft = $this->invoice($this->branchB, 'Draft', 200, '2026-05-01');
        SaleInvoiceItem::create([
            'sale_invoice_id' => $draft->id,
            'product_id' => $product->id,
            'gst_rate' => 0,
            'quantity' => 2,
            'unit_price' => 100,
            'amount' => 200,
        ]);

        $lines = app(SalesReportService::class)->invoiceLines($this->company->id);

        $this->assertCount(1, $lines);
        $line = $lines->first();
        $this->assertSame(5.0, (float) $line->quantity);
        $this->assertSame('Widget', $line->product->name);
        $this->assertSame('Alpha', $line->saleInvoice->branch->name);
        $this->assertSame('Priya Sharma', $line->saleInvoice->creator->name);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Party;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\User;
use App\Services\SaleInvoiceService;
use App\Services\SaleOrderService;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SaleInvoiceFromSaleOrderTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private FinancialYear $financialYear;

    private Branch $orderBranch;

    private Branch $otherBranch;

    private Party $customer;

    private Product $product;

    private User $user;

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

        $this->orderBranch = Branch::create(['company_id' => $this->company->id, 'primary_company_id' => $this->company->id, 'name' => 'Order Branch', 'code' => 'OB', 'status' => 'active']);
        $this->otherBranch = Branch::create(['company_id' => $this->company->id, 'name' => 'Other Branch', 'code' => 'XB', 'status' => 'active']);
        $this->customer = Party::create(['company_id' => $this->company->id, 'name' => 'Retail Co', 'is_vendor' => false, 'is_customer' => true]);
        $this->product = Product::create(['company_id' => $this->company->id, 'name' => 'Widget', 'status' => 'active']);
        $this->user = User::factory()->create();

        // Stock exists in the order's own branch, so the invoice can post out.
        app(StockMovementService::class)->postIn(
            $this->company, $this->financialYear, $this->orderBranch->id, $this->product, 10, '2026-05-01', null, null, null, $this->user, 50,
        );
    }

    private function confirmedOrder(): SaleOrder
    {
        $order = app(SaleOrderService::class)->create([
            'party_id' => $this->customer->id,
            'order_date' => '2026-05-01',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 4, 'unit_price' => 100, 'unit_type' => 'primary', 'notes' => null],
            ],
        ], $this->company, $this->orderBranch, $this->financialYear, $this->user);

        return app(SaleOrderService::class)->confirm($order);
    }

    public function test_creating_an_invoice_from_a_sale_order_auto_fills_lines_and_converts_the_order(): void
    {
        $order = $this->confirmedOrder();

        $invoice = app(SaleInvoiceService::class)->create([
            'party_id' => $this->customer->id,
            'invoice_date' => '2026-05-05',
            'sale_order_id' => $order->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 4, 'unit_price' => 100],
            ],
        ], $this->company, $this->otherBranch, $this->financialYear, $this->user);

        $this->assertSame('Converted', $order->fresh()->status);

        $line = $invoice->items()->first();
        $this->assertNull($line->delivery_challan_item_id);
        $this->assertSame(4.0, (float) $line->quantity);
        $this->assertNotNull($line->unit_cost, 'A direct-sale line must have its cost snapshotted, same as any other direct sale.');
    }

    public function test_the_invoice_uses_the_sale_orders_own_branch_not_the_currently_selected_branch(): void
    {
        $order = $this->confirmedOrder();

        // Pass a DIFFERENT branch as "current" — the invoice must still use the order's branch,
        // since that's where the order's stock commitment actually is.
        $invoice = app(SaleInvoiceService::class)->create([
            'party_id' => $this->customer->id,
            'invoice_date' => '2026-05-05',
            'sale_order_id' => $order->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 4, 'unit_price' => 100],
            ],
        ], $this->company, $this->otherBranch, $this->financialYear, $this->user);

        $this->assertSame($this->orderBranch->id, $invoice->branch_id);
    }

    public function test_a_converted_sale_order_cannot_be_converted_again(): void
    {
        $order = $this->confirmedOrder();
        app(SaleOrderService::class)->markConverted($order);

        $this->expectException(RuntimeException::class);

        app(SaleOrderService::class)->markConverted($order->fresh());
    }

    public function test_a_draft_sale_order_cannot_be_converted(): void
    {
        $order = app(SaleOrderService::class)->create([
            'party_id' => $this->customer->id,
            'order_date' => '2026-05-01',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 100, 'unit_type' => 'primary', 'notes' => null],
            ],
        ], $this->company, $this->orderBranch, $this->financialYear, $this->user);

        $this->expectException(RuntimeException::class);

        app(SaleOrderService::class)->markConverted($order);
    }
}

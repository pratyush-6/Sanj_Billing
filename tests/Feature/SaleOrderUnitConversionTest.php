<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Party;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Services\SaleOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleOrderUnitConversionTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private FinancialYear $financialYear;

    private Branch $branch;

    private Party $customer;

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

        $this->branch = Branch::create(['company_id' => $this->company->id, 'primary_company_id' => $this->company->id, 'name' => 'Main', 'code' => 'M', 'status' => 'active']);
        $this->customer = Party::create(['company_id' => $this->company->id, 'name' => 'Retail Co', 'is_vendor' => false, 'is_customer' => true]);
        $this->user = User::factory()->create();
    }

    public function test_selling_in_the_secondary_unit_stores_the_primary_unit_equivalent(): void
    {
        $bag = Unit::create(['company_id' => $this->company->id, 'name' => 'Bag']);
        $kg = Unit::create(['company_id' => $this->company->id, 'name' => 'Kg']);
        $product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Rice',
            'status' => 'active',
            'unit_id' => $bag->id,
            'secondary_unit_id' => $kg->id,
            'conversion_factor' => 25, // 1 Bag = 25 Kg
        ]);

        $order = app(SaleOrderService::class)->create([
            'party_id' => $this->customer->id,
            'order_date' => '2026-05-01',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 50, 'unit_price' => 10, 'unit_type' => 'secondary', 'notes' => null],
            ],
        ], $this->company, $this->branch, $this->financialYear, $this->user);

        $line = $order->items()->first();

        $this->assertSame(2.0, (float) $line->quantity); // 50 Kg == 2 Bag
        $this->assertSame(250.0, (float) $line->unit_price); // price per Bag
        $this->assertSame(500.0, (float) $line->amount); // unchanged either way
    }

    public function test_selling_in_the_primary_unit_stores_values_unchanged(): void
    {
        $bag = Unit::create(['company_id' => $this->company->id, 'name' => 'Bag']);
        $kg = Unit::create(['company_id' => $this->company->id, 'name' => 'Kg']);
        $product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Rice',
            'status' => 'active',
            'unit_id' => $bag->id,
            'secondary_unit_id' => $kg->id,
            'conversion_factor' => 25,
        ]);

        $order = app(SaleOrderService::class)->create([
            'party_id' => $this->customer->id,
            'order_date' => '2026-05-01',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 300, 'unit_type' => 'primary', 'notes' => null],
            ],
        ], $this->company, $this->branch, $this->financialYear, $this->user);

        $line = $order->items()->first();

        $this->assertSame(3.0, (float) $line->quantity);
        $this->assertSame(300.0, (float) $line->unit_price);
        $this->assertSame(900.0, (float) $line->amount);
    }

    public function test_unit_type_is_ignored_when_the_product_has_no_secondary_unit(): void
    {
        $product = Product::create(['company_id' => $this->company->id, 'name' => 'Simple Item', 'status' => 'active']);

        $order = app(SaleOrderService::class)->create([
            'party_id' => $this->customer->id,
            'order_date' => '2026-05-01',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4, 'unit_price' => 50, 'unit_type' => 'secondary', 'notes' => null],
            ],
        ], $this->company, $this->branch, $this->financialYear, $this->user);

        $line = $order->items()->first();

        $this->assertSame(4.0, (float) $line->quantity);
        $this->assertSame(50.0, (float) $line->unit_price);
    }
}

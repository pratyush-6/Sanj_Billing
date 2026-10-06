<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockAdjustmentService;
use App\Services\StockLevelService;
use App\Services\StockTransferService;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use RuntimeException;
use Tests\TestCase;

class BranchStockTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private FinancialYear $financialYear;

    private User $user;

    private Product $product;

    private Branch $branchA;

    private Branch $branchB;

    private StockMovementService $movements;

    private StockLevelService $levels;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Test Co', 'status' => 'active']);

        $this->financialYear = FinancialYear::create([
            'company_id' => $this->company->id,
            'name' => 'FY 2026-27',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create();

        $this->product = Product::create(['company_id' => $this->company->id, 'name' => 'Rice', 'status' => 'active']);

        $this->branchA = Branch::create(['company_id' => $this->company->id, 'name' => 'Alpha', 'code' => 'A', 'status' => 'active']);
        $this->branchB = Branch::create(['company_id' => $this->company->id, 'name' => 'Beta', 'code' => 'B', 'status' => 'active']);

        $this->movements = app(StockMovementService::class);
        $this->levels = app(StockLevelService::class);
    }

    private function stockIn(?Branch $branch, float $quantity, float $unitCost): void
    {
        $this->movements->postIn($this->company, $this->financialYear, $branch?->id, $this->product, $quantity, '2026-05-01', null, null, null, $this->user, $unitCost);
    }

    public function test_stock_is_isolated_per_branch(): void
    {
        $this->stockIn($this->branchA, 10, 100);

        $this->assertSame(10.0, $this->levels->currentStockFor($this->product, $this->branchA->id));
        $this->assertSame(0.0, $this->levels->currentStockFor($this->product, $this->branchB->id));
    }

    public function test_a_branch_cannot_issue_more_than_it_holds(): void
    {
        $this->stockIn($this->branchA, 5, 100);

        $this->expectException(RuntimeException::class);

        $this->movements->postOut($this->company, $this->financialYear, $this->branchB->id, $this->product, 1, '2026-05-02', null, null, null, $this->user);
    }

    public function test_an_issue_within_branch_stock_is_allowed_and_reduces_only_that_branch(): void
    {
        $this->stockIn($this->branchA, 5, 100);
        $this->stockIn($this->branchB, 5, 100);

        $this->movements->postOut($this->company, $this->financialYear, $this->branchA->id, $this->product, 3, '2026-05-02', null, null, null, $this->user);

        $this->assertSame(2.0, $this->levels->currentStockFor($this->product, $this->branchA->id));
        $this->assertSame(5.0, $this->levels->currentStockFor($this->product, $this->branchB->id));
    }

    public function test_unassigned_stock_cannot_be_sold_from_a_branch(): void
    {
        $this->stockIn(null, 10, 100);

        $this->expectException(RuntimeException::class);

        $this->movements->postOut($this->company, $this->financialYear, $this->branchA->id, $this->product, 1, '2026-05-02', null, null, null, $this->user);
    }

    public function test_average_cost_is_calculated_per_branch(): void
    {
        $this->stockIn($this->branchA, 10, 100);
        $this->stockIn($this->branchB, 10, 200);

        $this->assertSame(100.0, $this->levels->averageCostFor($this->product, $this->branchA->id));
        $this->assertSame(200.0, $this->levels->averageCostFor($this->product, $this->branchB->id));
    }

    public function test_assigning_unassigned_stock_moves_it_at_the_same_cost(): void
    {
        $this->stockIn(null, 10, 50);

        app(StockAdjustmentService::class)->assignUnassigned(
            $this->company, $this->financialYear, $this->product, 4, $this->branchA, '2026-05-03', 'Opening Stock', $this->user,
        );

        $this->assertSame(6.0, $this->levels->currentStockFor($this->product, null));
        $this->assertSame(4.0, $this->levels->currentStockFor($this->product, $this->branchA->id));
        $this->assertSame(50.0, $this->levels->averageCostFor($this->product, $this->branchA->id));
    }

    public function test_company_wide_stock_equals_the_sum_of_every_branch_and_unassigned(): void
    {
        $this->stockIn(null, 3, 10);
        $this->stockIn($this->branchA, 5, 10);
        $this->stockIn($this->branchB, 7, 10);

        $this->assertSame(15.0, $this->levels->currentStockFor($this->product, null, true));
    }

    public function test_a_transfer_moves_stock_at_the_source_cost(): void
    {
        $this->stockIn($this->branchA, 10, 100);

        $transfer = app(StockTransferService::class)->create(
            $this->company, $this->financialYear, $this->branchA, $this->branchB, '2026-05-04',
            [['product_id' => $this->product->id, 'quantity' => 4]], null, $this->user,
        );

        $this->assertSame(6.0, $this->levels->currentStockFor($this->product, $this->branchA->id));
        $this->assertSame(4.0, $this->levels->currentStockFor($this->product, $this->branchB->id));
        $this->assertSame(100.0, $this->levels->averageCostFor($this->product, $this->branchB->id));
        $this->assertSame(400.0, (float) $transfer->items()->firstOrFail()->total_cost);
    }

    public function test_a_transfer_cannot_exceed_the_source_branch_stock(): void
    {
        $this->stockIn($this->branchA, 2, 100);

        $this->expectException(RuntimeException::class);

        app(StockTransferService::class)->create(
            $this->company, $this->financialYear, $this->branchA, $this->branchB, '2026-05-04',
            [['product_id' => $this->product->id, 'quantity' => 3]], null, $this->user,
        );
    }

    public function test_a_transfer_to_the_same_branch_is_rejected(): void
    {
        $this->stockIn($this->branchA, 2, 100);

        $this->expectException(RuntimeException::class);

        app(StockTransferService::class)->create(
            $this->company, $this->financialYear, $this->branchA, $this->branchA, '2026-05-04',
            [['product_id' => $this->product->id, 'quantity' => 1]], null, $this->user,
        );
    }

    public function test_old_unassigned_records_stay_visible_under_every_branch(): void
    {
        $this->stockIn(null, 3, 10);
        $this->stockIn($this->branchA, 5, 10);
        $this->stockIn($this->branchB, 7, 10);

        $this->company->users()->attach($this->user);
        $this->branchA->users()->attach($this->user);
        $this->branchB->users()->attach($this->user);

        $this->actingAs($this->user);
        Session::put(['current_company_id' => $this->company->id, 'current_branch_id' => $this->branchA->id]);

        $visibleInA = scope_to_branch(StockMovement::query())->count();

        Session::put(['current_branch_id' => $this->branchB->id]);
        $visibleInB = scope_to_branch(StockMovement::query())->count();

        $this->assertSame(2, $visibleInA);
        $this->assertSame(2, $visibleInB);
    }

    public function test_switching_to_another_companys_branch_is_rejected(): void
    {
        $otherCompany = Company::create(['name' => 'Other Co', 'status' => 'active']);
        $otherBranch = Branch::create(['company_id' => $otherCompany->id, 'name' => 'Elsewhere', 'code' => 'X', 'status' => 'active']);

        $this->company->users()->attach($this->user);
        $this->branchA->users()->attach($this->user);

        $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post(route('branches.switch', $otherBranch))
            ->assertForbidden();
    }

    public function test_switching_to_a_branch_not_assigned_to_the_user_is_rejected(): void
    {
        $this->company->users()->attach($this->user);
        $this->branchA->users()->attach($this->user);

        $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post(route('branches.switch', $this->branchB))
            ->assertForbidden();
    }

    public function test_switching_to_an_assigned_branch_succeeds(): void
    {
        $this->company->users()->attach($this->user);
        $this->branchA->users()->attach($this->user);

        $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post(route('branches.switch', $this->branchA))
            ->assertRedirect(route('dashboard'));

        $this->assertSame($this->branchA->id, session('current_branch_id'));
    }
}

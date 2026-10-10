<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\BranchContextService;
use App\Services\BranchService;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class HeadBranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_company_auto_creates_one_head_branch(): void
    {
        $company = app(CompanyService::class)->save(['name' => 'Acme', 'status' => 'active']);

        $branches = Branch::where('company_id', $company->id)->get();

        $this->assertCount(1, $branches);
        $this->assertTrue($branches->first()->is_primary);
        $this->assertSame('Head Office', $branches->first()->name);
    }

    public function test_a_companys_first_branch_becomes_head_automatically(): void
    {
        $company = Company::create(['name' => 'No Default Masters Co', 'status' => 'active']);

        $branch = app(BranchService::class)->create(['name' => 'Alpha', 'code' => 'A', 'status' => 'active'], $company);

        $this->assertTrue($branch->fresh()->is_primary);
    }

    public function test_a_second_branch_is_not_head_by_default(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $service = app(BranchService::class);

        $first = $service->create(['name' => 'Alpha', 'code' => 'A', 'status' => 'active'], $company);
        $second = $service->create(['name' => 'Beta', 'code' => 'B', 'status' => 'active'], $company);

        $this->assertTrue($first->fresh()->is_primary);
        $this->assertFalse($second->fresh()->is_primary);
    }

    public function test_making_a_branch_head_unsets_the_previous_head(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $service = app(BranchService::class);

        $first = $service->create(['name' => 'Alpha', 'code' => 'A', 'status' => 'active'], $company);
        $second = $service->create(['name' => 'Beta', 'code' => 'B', 'status' => 'active'], $company);

        $service->makeHead($second);

        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame(1, Branch::where('company_id', $company->id)->head()->count());
    }

    public function test_the_database_rejects_two_heads_for_one_company(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        app(BranchService::class)->create(['name' => 'Alpha', 'code' => 'A', 'status' => 'active'], $company);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('branches')->insert([
            'company_id' => $company->id,
            'primary_company_id' => $company->id,
            'name' => 'Beta',
            'code' => 'B',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_default_branch_context_picks_the_head_branch_even_when_another_sorts_first_alphabetically(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $service = app(BranchService::class);

        $head = $service->create(['name' => 'Zulu Branch', 'code' => 'Z', 'status' => 'active'], $company);
        $alpha = $service->create(['name' => 'Alpha Branch', 'code' => 'A', 'status' => 'active'], $company);

        $user = User::factory()->create();
        $company->users()->attach($user);
        $head->users()->attach($user);
        $alpha->users()->attach($user);

        $this->actingAs($user);
        session(['current_company_id' => $company->id]);

        $current = app(BranchContextService::class)->current();

        $this->assertSame($head->id, $current->id);
    }

    public function test_deactivating_the_head_branch_is_rejected(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $head = app(BranchService::class)->create(['name' => 'Alpha', 'code' => 'A', 'status' => 'active'], $company);

        $this->expectException(RuntimeException::class);

        app(BranchService::class)->update($head, ['status' => 'inactive']);
    }

    public function test_deactivating_a_non_head_branch_is_allowed(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $service = app(BranchService::class);
        $service->create(['name' => 'Alpha', 'code' => 'A', 'status' => 'active'], $company);
        $second = $service->create(['name' => 'Beta', 'code' => 'B', 'status' => 'active'], $company);

        $service->update($second, ['status' => 'inactive']);

        $this->assertSame('inactive', $second->fresh()->status);
    }
}

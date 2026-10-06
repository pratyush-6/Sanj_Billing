<?php

use App\Models\Branch;
use App\Models\Company;
use App\Services\BranchContextService;
use App\Services\CompanyContextService;

if (! function_exists('current_company')) {
    function current_company(): ?Company
    {
        return app(CompanyContextService::class)->current();
    }
}

if (! function_exists('current_company_or_fail')) {
    function current_company_or_fail(): Company
    {
        return current_company() ?? abort(404, 'No company selected.');
    }
}

if (! function_exists('current_branch')) {
    function current_branch(): ?Branch
    {
        return app(BranchContextService::class)->current();
    }
}

if (! function_exists('current_branch_or_fail')) {
    function current_branch_or_fail(): Branch
    {
        return current_branch() ?? abort(422, 'Select a branch before recording transactions.');
    }
}

if (! function_exists('scope_to_branch')) {
    function scope_to_branch(\Illuminate\Database\Eloquent\Builder $query, string $column = 'branch_id'): \Illuminate\Database\Eloquent\Builder
    {
        return app(BranchContextService::class)->applyTo($query, $column);
    }
}

<?php

use App\Models\Company;
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

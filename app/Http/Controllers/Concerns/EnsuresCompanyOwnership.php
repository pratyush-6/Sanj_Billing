<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;

trait EnsuresCompanyOwnership
{
    /**
     * Guard a route-bound model against cross-company access. Aborts with
     * 404 (not 403) so a user in one company can't even confirm a record
     * from another company exists.
     */
    protected function ensureBelongsToCurrentCompany(Model $model, string $companyIdAttribute = 'company_id'): void
    {
        abort_unless(
            $model->{$companyIdAttribute} === current_company()?->id,
            404
        );
    }
}

<?php

namespace App\Services\Concerns;

use App\Models\Company;
use App\Models\FinancialYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait GeneratesSequentialNumbers
{
    protected function generateSequentialNumber(
        string $modelClass,
        string $prefix,
        Company $company,
        FinancialYear $financialYear,
        string $companyColumn = 'company_id',
        string $financialYearColumn = 'financial_year_id',
    ): string {
        $fyCode = Str::of($financialYear->name)->after('FY ')->replace([' ', '/'], '-')->__toString();

        return DB::transaction(function () use ($modelClass, $prefix, $company, $financialYear, $companyColumn, $financialYearColumn, $fyCode) {
            $count = $modelClass::where($companyColumn, $company->id)
                ->where($financialYearColumn, $financialYear->id)
                ->lockForUpdate()
                ->count();

            return "{$prefix}-{$fyCode}-".str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        });
    }
}

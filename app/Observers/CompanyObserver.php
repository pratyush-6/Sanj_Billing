<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\AccountingService;

class CompanyObserver
{
    public function __construct(private AccountingService $accountingService) {}

    public function created(Company $company): void
    {
        $this->accountingService->seedChartOfAccounts($company);
    }
}

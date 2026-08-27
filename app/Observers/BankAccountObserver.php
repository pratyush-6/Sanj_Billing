<?php

namespace App\Observers;

use App\Models\BankAccount;
use App\Services\AccountingService;

class BankAccountObserver
{
    public function __construct(private AccountingService $accountingService) {}

    public function created(BankAccount $bankAccount): void
    {
        $this->accountingService->mapBankAccount($bankAccount);
    }

    public function updated(BankAccount $bankAccount): void
    {
        if ($bankAccount->wasChanged('account_name')) {
            $this->accountingService->mapBankAccount($bankAccount);
        }
    }
}

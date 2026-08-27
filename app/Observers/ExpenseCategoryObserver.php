<?php

namespace App\Observers;

use App\Models\ExpenseCategory;
use App\Services\AccountingService;

class ExpenseCategoryObserver
{
    public function __construct(private AccountingService $accountingService) {}

    public function created(ExpenseCategory $category): void
    {
        $this->accountingService->mapExpenseCategory($category);
    }

    public function updated(ExpenseCategory $category): void
    {
        if ($category->wasChanged('name')) {
            $this->accountingService->mapExpenseCategory($category);
        }
    }
}

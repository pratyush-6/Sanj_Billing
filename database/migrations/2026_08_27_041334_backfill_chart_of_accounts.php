<?php

use App\Models\BankAccount;
use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Services\AccountingService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $accounting = app(AccountingService::class);

        Company::orderBy('id')->each(function (Company $company) use ($accounting) {
            $accounting->seedChartOfAccounts($company);
        });

        ExpenseCategory::whereNull('account_id')->orderBy('id')->each(function (ExpenseCategory $category) use ($accounting) {
            $accounting->mapExpenseCategory($category);
        });

        BankAccount::whereNull('account_id')->orderBy('id')->each(function (BankAccount $bankAccount) use ($accounting) {
            $accounting->mapBankAccount($bankAccount);
        });
    }

    public function down(): void
    {
        // No-op: chart of accounts and mappings are additive and safe to leave in place.
    }
};

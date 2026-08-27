<?php

namespace App\Providers;

use App\Models\BankAccount;
use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Observers\BankAccountObserver;
use App\Observers\CompanyObserver;
use App\Observers\ExpenseCategoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Company::observe(CompanyObserver::class);
        ExpenseCategory::observe(ExpenseCategoryObserver::class);
        BankAccount::observe(BankAccountObserver::class);
    }
}

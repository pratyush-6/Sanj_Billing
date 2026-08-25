<?php

use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\FinancialYearController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Expense\ExpenseCategoryController;
use App\Http\Controllers\Expense\ExpenseController;
use App\Http\Controllers\Expense\ExpenseSubCategoryController;
use App\Http\Controllers\Masters\BankAccountController;
use App\Http\Controllers\Masters\PaymentMethodController;
use App\Http\Controllers\Masters\UnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Vendor\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/companies/{company}/switch', [CompanyController::class, 'switch'])->name('companies.switch');

    Route::middleware('can:companies.manage')->group(function () {
        Route::resource('companies', CompanyController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:financial-years.manage')->group(function () {
        Route::get('/settings/financial-years', [FinancialYearController::class, 'index'])->name('financial-years.index');
        Route::get('/settings/financial-years/create', [FinancialYearController::class, 'create'])->name('financial-years.create');
        Route::post('/settings/financial-years', [FinancialYearController::class, 'store'])->name('financial-years.store');
        Route::post('/settings/financial-years/{financialYear}/activate', [FinancialYearController::class, 'activate'])->name('financial-years.activate');
        Route::post('/settings/financial-years/{financialYear}/lock', [FinancialYearController::class, 'lock'])->name('financial-years.lock');
        Route::post('/settings/financial-years/{financialYear}/unlock', [FinancialYearController::class, 'unlock'])->name('financial-years.unlock');
        Route::post('/settings/financial-years/{financialYear}/close', [FinancialYearController::class, 'close'])->name('financial-years.close');
    });

    Route::middleware('can:users.manage')->group(function () {
        Route::resource('/settings/users', UserController::class)
            ->parameters(['settings/users' => 'user'])
            ->except(['show', 'destroy'])
            ->names('settings.users');
    });

    Route::middleware('can:audit-logs.view')->group(function () {
        Route::get('/settings/audit-logs', [AuditLogController::class, 'index'])->name('settings.audit-logs.index');
    });

    Route::middleware('can:expense-categories.manage')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show', 'destroy']);
        Route::resource('expense-sub-categories', ExpenseSubCategoryController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:vendors.manage')->group(function () {
        Route::resource('vendors', VendorController::class)->except(['destroy']);
    });

    Route::middleware('can:masters.manage')->group(function () {
        Route::resource('units', UnitController::class)->except(['show', 'destroy']);
        Route::resource('payment-methods', PaymentMethodController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:bank-accounts.manage')->group(function () {
        Route::resource('bank-accounts', BankAccountController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:expenses.view')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    });

    Route::middleware('can:expenses.manage')->group(function () {
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::post('/expenses/{expense}/cancel', [ExpenseController::class, 'cancel'])->name('expenses.cancel');
    });
});

require __DIR__.'/auth.php';

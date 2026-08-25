<?php

use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\FinancialYearController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\UserController;
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

    Route::middleware('can:companies.manage')->group(function () {
        Route::get('/settings/company', [CompanyController::class, 'edit'])->name('company.edit');
        Route::put('/settings/company', [CompanyController::class, 'update'])->name('company.update');
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
});

require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Expense;

class DashboardController extends Controller
{
    public function index()
    {
        $company = Company::first();
        $activeFinancialYear = $company?->activeFinancialYear();

        $stats = null;

        if ($company && $activeFinancialYear) {
            $baseQuery = Expense::where('company_id', $company->id)
                ->where('financial_year_id', $activeFinancialYear->id)
                ->where('status', '!=', 'Cancelled');

            $stats = [
                'today' => (clone $baseQuery)->whereDate('expense_date', today())->sum('total_amount'),
                'this_month' => (clone $baseQuery)->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('total_amount'),
                'financial_year' => (clone $baseQuery)->sum('total_amount'),
            ];
        }

        return view('dashboard', [
            'company' => $company,
            'activeFinancialYear' => $activeFinancialYear,
            'stats' => $stats,
        ]);
    }
}

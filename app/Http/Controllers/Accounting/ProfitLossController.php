<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    public function __construct(private AccountingService $accountingService) {}

    public function index(Request $request)
    {
        $company = current_company_or_fail();
        $financialYears = $company->financialYears()->orderByDesc('start_date')->get();

        $financialYear = $request->filled('financial_year_id')
            ? $financialYears->firstWhere('id', $request->integer('financial_year_id'))
            : $company->activeFinancialYear();

        $data = $financialYear ? $this->accountingService->profitAndLoss($company, $financialYear) : null;

        return view('accounting.profit-loss.index', [
            'data' => $data,
            'financialYear' => $financialYear,
            'financialYears' => $financialYears,
        ]);
    }
}

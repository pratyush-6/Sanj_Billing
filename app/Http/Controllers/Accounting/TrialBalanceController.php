<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class TrialBalanceController extends Controller
{
    public function __construct(private AccountingService $accountingService) {}

    public function index(Request $request)
    {
        $company = current_company_or_fail();
        $asOfDate = $request->filled('as_of_date') ? $request->string('as_of_date')->toString() : now()->toDateString();

        $rows = $this->accountingService->trialBalance($company, $asOfDate);

        return view('accounting.trial-balance.index', [
            'rows' => $rows,
            'totalDebit' => $rows->sum('debit'),
            'totalCredit' => $rows->sum('credit'),
            'asOfDate' => $asOfDate,
        ]);
    }
}

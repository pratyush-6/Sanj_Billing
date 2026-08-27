<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function __construct(private AccountingService $accountingService) {}

    public function index(Request $request)
    {
        $company = current_company_or_fail();

        $accounts = Account::where('company_id', $company->id)
            ->whereDoesntHave('children')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $account = $accounts->firstWhere('id', $request->integer('account_id')) ?? $accounts->first();

        $dateFrom = $request->filled('date_from') ? $request->string('date_from')->toString() : null;
        $dateTo = $request->filled('date_to') ? $request->string('date_to')->toString() : null;

        $ledger = $account ? $this->accountingService->ledger($account, $dateFrom, $dateTo) : null;

        return view('accounting.ledger.index', [
            'accounts' => $accounts,
            'account' => $account,
            'ledger' => $ledger,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}

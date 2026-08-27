<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountingService;

class ChartOfAccountsController extends Controller
{
    public function __construct(private AccountingService $accountingService) {}

    public function index()
    {
        $company = current_company_or_fail();
        $accounts = Account::where('company_id', $company->id)->orderBy('name')->get();
        $balances = $this->accountingService->liveBalances($accounts);

        $tree = $accounts->whereNull('parent_account_id')
            ->sortBy('type')
            ->map(fn ($root) => $this->withChildren($root, $accounts, $balances))
            ->values();

        return view('accounting.chart-of-accounts.index', ['tree' => $tree]);
    }

    private function withChildren(Account $account, $all, array $balances): object
    {
        return (object) [
            'account' => $account,
            'balance' => $balances[$account->id] ?? 0.0,
            'children' => $all->where('parent_account_id', $account->id)
                ->map(fn ($child) => $this->withChildren($child, $all, $balances))
                ->values(),
        ];
    }
}

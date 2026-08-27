<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest;
use App\Models\BankAccount;
use App\Services\AccountingService;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class BankAccountController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(
        private MasterDataService $masterDataService,
        private AccountingService $accountingService,
    ) {}

    public function index()
    {
        $bankAccounts = BankAccount::where('company_id', current_company()?->id)->with('account')->orderBy('account_name')->get();
        $accountBalances = $this->accountingService->liveBalances($bankAccounts->pluck('account')->filter());
        $balances = $bankAccounts->mapWithKeys(fn ($bankAccount) => [
            $bankAccount->id => $bankAccount->account_id ? ($accountBalances[$bankAccount->account_id] ?? 0.0) : 0.0,
        ]);

        return view('masters.bank-accounts.index', ['bankAccounts' => $bankAccounts, 'balances' => $balances]);
    }

    public function create()
    {
        return view('masters.bank-accounts.create');
    }

    public function store(BankAccountRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(BankAccount::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Bank Account');

        return redirect()->route('bank-accounts.index')->with('status', 'Account created.');
    }

    public function edit(BankAccount $bankAccount)
    {
        $this->ensureBelongsToCurrentCompany($bankAccount);

        return view('masters.bank-accounts.edit', ['bankAccount' => $bankAccount]);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($bankAccount);

        $data = $request->validated();
        unset($data['opening_balance']);

        $this->masterDataService->update($bankAccount, $data, 'Bank Account');

        return redirect()->route('bank-accounts.index')->with('status', 'Account updated.');
    }
}

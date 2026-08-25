<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest;
use App\Models\BankAccount;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class BankAccountController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $bankAccounts = BankAccount::where('company_id', current_company()?->id)->orderBy('account_name')->get();

        return view('masters.bank-accounts.index', ['bankAccounts' => $bankAccounts]);
    }

    public function create()
    {
        return view('masters.bank-accounts.create');
    }

    public function store(BankAccountRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $data = $request->validated();
        $data['current_balance'] = $data['opening_balance'];

        $this->masterDataService->create(BankAccount::class, [
            ...$data,
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

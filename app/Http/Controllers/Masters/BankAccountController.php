<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest;
use App\Models\BankAccount;
use App\Models\Company;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class BankAccountController extends Controller
{
    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        return view('masters.bank-accounts.index', ['bankAccounts' => BankAccount::orderBy('account_name')->get()]);
    }

    public function create()
    {
        return view('masters.bank-accounts.create');
    }

    public function store(BankAccountRequest $request): RedirectResponse
    {
        $company = Company::firstOrFail();
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
        return view('masters.bank-accounts.edit', ['bankAccount' => $bankAccount]);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $data = $request->validated();
        unset($data['opening_balance']);

        $this->masterDataService->update($bankAccount, $data, 'Bank Account');

        return redirect()->route('bank-accounts.index')->with('status', 'Account updated.');
    }
}

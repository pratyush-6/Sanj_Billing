<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Services\CompanyContextService;
use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyService $companyService,
        private CompanyContextService $companyContext,
    ) {}

    public function index()
    {
        return view('company.index', [
            'companies' => $this->companyContext->accessibleCompanies(),
            'currentCompany' => current_company(),
        ]);
    }

    public function create()
    {
        return view('company.create');
    }

    public function store(CompanyRequest $request): RedirectResponse
    {
        $company = $this->companyService->save($request->validated());

        $this->companyContext->switchTo($company->id);

        return redirect()->route('companies.index')->with('status', 'Company created.');
    }

    public function edit(Company $company)
    {
        abort_unless($this->companyContext->accessibleCompanies()->contains('id', $company->id), 404);

        return view('company.edit', ['company' => $company]);
    }

    public function update(CompanyRequest $request, Company $company): RedirectResponse
    {
        abort_unless($this->companyContext->accessibleCompanies()->contains('id', $company->id), 404);

        $this->companyService->save($request->validated(), $company);

        return redirect()->route('companies.index')->with('status', 'Company profile saved.');
    }

    public function switch(Company $company): RedirectResponse
    {
        if (! $this->companyContext->switchTo($company->id)) {
            abort(403);
        }

        return redirect()->route('dashboard')->with('status', "Switched to {$company->name}.");
    }
}

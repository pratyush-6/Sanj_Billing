<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;

class CompanyController extends Controller
{
    public function __construct(private CompanyService $companyService) {}

    public function edit()
    {
        $company = Company::first();

        return view('company.edit', ['company' => $company]);
    }

    public function update(CompanyRequest $request)
    {
        $company = Company::first();

        $this->companyService->save($request->validated(), $company);

        return redirect()->route('company.edit')->with('status', 'Company profile saved.');
    }
}

<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinancialYearRequest;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Services\FinancialYearService;
use Illuminate\Http\RedirectResponse;

class FinancialYearController extends Controller
{
    public function __construct(private FinancialYearService $financialYearService) {}

    public function index()
    {
        $company = Company::first();

        $financialYears = $company
            ? $company->financialYears()->orderByDesc('start_date')->get()
            : collect();

        return view('financial-years.index', [
            'company' => $company,
            'financialYears' => $financialYears,
        ]);
    }

    public function create()
    {
        return view('financial-years.create', ['company' => Company::firstOrFail()]);
    }

    public function store(FinancialYearRequest $request): RedirectResponse
    {
        $company = Company::firstOrFail();

        $financialYear = $this->financialYearService->create([
            ...$request->validated(),
            'company_id' => $company->id,
        ]);

        if (! $company->activeFinancialYear()) {
            $this->financialYearService->activate($financialYear);
        }

        return redirect()->route('financial-years.index')->with('status', 'Financial year created.');
    }

    public function activate(FinancialYear $financialYear): RedirectResponse
    {
        $this->financialYearService->activate($financialYear);

        return redirect()->route('financial-years.index')->with('status', 'Financial year activated.');
    }

    public function lock(FinancialYear $financialYear): RedirectResponse
    {
        $this->financialYearService->lock($financialYear);

        return redirect()->route('financial-years.index')->with('status', 'Financial year locked.');
    }

    public function unlock(FinancialYear $financialYear): RedirectResponse
    {
        $this->financialYearService->unlock($financialYear);

        return redirect()->route('financial-years.index')->with('status', 'Financial year unlocked.');
    }

    public function close(FinancialYear $financialYear): RedirectResponse
    {
        $this->financialYearService->close($financialYear);

        return redirect()->route('financial-years.index')->with('status', 'Financial year closed.');
    }
}

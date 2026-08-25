<?php

namespace App\Http\Controllers;

use App\Models\Company;

class DashboardController extends Controller
{
    public function index()
    {
        $company = Company::first();
        $activeFinancialYear = $company?->activeFinancialYear();

        return view('dashboard', [
            'company' => $company,
            'activeFinancialYear' => $activeFinancialYear,
        ]);
    }
}

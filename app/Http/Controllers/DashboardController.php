<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Services\AccountingService;
use App\Services\InventoryReportService;
use App\Services\ReportService;

class DashboardController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private AccountingService $accountingService,
        private InventoryReportService $inventoryReports,
    ) {}

    public function index()
    {
        $company = current_company();
        $activeFinancialYear = $company?->activeFinancialYear();

        $stats = null;
        $charts = null;
        $financials = null;
        $lowStock = null;

        if ($company && $activeFinancialYear) {
            $baseQuery = Expense::where('company_id', $company->id)
                ->where('financial_year_id', $activeFinancialYear->id)
                ->where('status', '!=', 'Cancelled');

            $stats = [
                'today' => (clone $baseQuery)->whereDate('expense_date', today())->sum('total_amount'),
                'this_month' => (clone $baseQuery)->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('total_amount'),
                'financial_year' => (clone $baseQuery)->sum('total_amount'),
            ];

            if (auth()->user()->can('reports.view')) {
                $categoryRows = $this->reports->categoryWise($company->id, null, null, $activeFinancialYear->id);
                $paymentRows = $this->reports->paymentMethodWise($company->id, null, null, $activeFinancialYear->id);
                $vendorRows = $this->reports->vendorWise($company->id, null, null, $activeFinancialYear->id)
                    ->filter(fn ($row) => $row->vendor_id > 0)
                    ->take(6);

                $charts = [
                    'category' => $categoryRows,
                    'payment' => $paymentRows,
                    'topVendors' => $vendorRows,
                    'monthlyTrend' => $this->reports->monthlyTrend($company->id, 6),
                ];
            }

            if (auth()->user()->can('accounting.view')) {
                $bankAccounts = BankAccount::where('company_id', $company->id)->with('account')->get();
                $accountBalances = $this->accountingService->liveBalances($bankAccounts->pluck('account')->filter());

                $balanceFor = fn ($bankAccount) => $accountBalances[$bankAccount->account_id] ?? 0.0;

                $liabilityAccounts = Account::where('company_id', $company->id)->where('type', 'Liability')->whereDoesntHave('children')->get();
                $payable = array_sum($this->accountingService->liveBalances($liabilityAccounts));

                $profitAndLoss = $this->accountingService->profitAndLoss($company, $activeFinancialYear);

                $financials = [
                    'net_profit' => $profitAndLoss['profit_before_tax'],
                    'cash_balance' => $bankAccounts->where('account_type', 'cash')->sum($balanceFor),
                    'bank_balance' => $bankAccounts->where('account_type', '!=', 'cash')->sum($balanceFor),
                    'payable' => $payable,
                ];
            }
        }

        if ($company && auth()->user()->can('inventory.view')) {
            $lowStock = $this->inventoryReports->lowStock($company)->take(6);
        }

        return view('dashboard', [
            'company' => $company,
            'activeFinancialYear' => $activeFinancialYear,
            'stats' => $stats,
            'charts' => $charts,
            'financials' => $financials,
            'lowStock' => $lowStock,
        ]);
    }
}

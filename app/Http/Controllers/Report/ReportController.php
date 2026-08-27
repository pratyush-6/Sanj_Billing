<?php

namespace App\Http\Controllers\Report;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\FinancialYear;
use App\Services\AccountingService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private AccountingService $accountingService,
    ) {}

    public function index()
    {
        return view('reports.index', [
            'financialYears' => $this->financialYears(),
        ]);
    }

    public function categoryWise(Request $request)
    {
        $company = current_company_or_fail();
        [$dateFrom, $dateTo, $financialYearId] = $this->resolveFilters($request);

        $rows = $this->reports->categoryWise($company->id, $dateFrom, $dateTo, $financialYearId);

        return view('reports.category-wise', [
            'rows' => $rows,
            'total' => (float) $rows->sum('total'),
            'filters' => $request->only(['date_from', 'date_to', 'financial_year_id']),
            'financialYears' => $this->financialYears(),
        ]);
    }

    public function vendorWise(Request $request)
    {
        $company = current_company_or_fail();
        [$dateFrom, $dateTo, $financialYearId] = $this->resolveFilters($request);

        $rows = $this->reports->vendorWise($company->id, $dateFrom, $dateTo, $financialYearId);

        return view('reports.vendor-wise', [
            'rows' => $rows,
            'total' => (float) $rows->sum('total'),
            'filters' => $request->only(['date_from', 'date_to', 'financial_year_id']),
            'financialYears' => $this->financialYears(),
        ]);
    }

    public function paymentWise(Request $request)
    {
        $company = current_company_or_fail();
        [$dateFrom, $dateTo, $financialYearId] = $this->resolveFilters($request);

        $paymentMethodRows = $this->reports->paymentMethodWise($company->id, $dateFrom, $dateTo, $financialYearId);
        $bankAccountRows = $this->reports->bankAccountWise($company->id, $dateFrom, $dateTo, $financialYearId);

        return view('reports.payment-wise', [
            'paymentMethodRows' => $paymentMethodRows,
            'bankAccountRows' => $bankAccountRows,
            'filters' => $request->only(['date_from', 'date_to', 'financial_year_id']),
            'financialYears' => $this->financialYears(),
        ]);
    }

    public function monthlyComparison(Request $request)
    {
        $company = current_company_or_fail();
        $financialYear = $this->resolveFinancialYear($request, $company->id);

        $data = $financialYear ? $this->reports->monthlyComparison($company->id, $financialYear) : ['months' => collect(), 'rows' => collect()];

        return view('reports.monthly-comparison', [
            'months' => $data['months'],
            'rows' => $data['rows'],
            'financialYear' => $financialYear,
            'financialYears' => $this->financialYears(),
        ]);
    }

    public function financialYearSummary(Request $request)
    {
        $company = current_company_or_fail();
        $financialYear = $this->resolveFinancialYear($request, $company->id);

        $data = $financialYear
            ? $this->reports->financialYearSummary($company->id, $financialYear)
            : ['rows' => collect(), 'total' => 0];

        return view('reports.financial-year-summary', [
            'rows' => $data['rows'],
            'total' => $data['total'],
            'financialYear' => $financialYear,
            'financialYears' => $this->financialYears(),
        ]);
    }

    public function daily(Request $request)
    {
        $company = current_company_or_fail();
        $date = $request->string('date')->toString() ?: now()->toDateString();

        $data = $this->reports->dailySummary($company->id, $date);

        return view('reports.daily', $data);
    }

    public function monthly(Request $request)
    {
        $company = current_company_or_fail();
        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $from = $month.'-01';
        $to = date('Y-m-t', strtotime($from));

        $data = $this->reports->monthlySummary($company->id, $from, $to);

        return view('reports.monthly', [...$data, 'month' => $month]);
    }

    public function bankCashBook(Request $request)
    {
        $company = current_company_or_fail();
        $bankAccounts = BankAccount::where('company_id', $company->id)->orderBy('account_name')->get();
        $bankAccount = $bankAccounts->firstWhere('id', $request->integer('bank_account_id')) ?? $bankAccounts->first();

        [$dateFrom, $dateTo] = $this->resolveFilters($request);
        $dateFrom = $dateFrom ?: now()->startOfMonth()->toDateString();
        $dateTo = $dateTo ?: now()->toDateString();

        $transactions = collect();
        $closingBalance = 0;
        $openingBalance = 0;

        if ($bankAccount?->account) {
            $ledger = $this->accountingService->ledger($bankAccount->account, $dateFrom, $dateTo);
            $openingBalance = $ledger['opening_balance'];
            $closingBalance = $ledger['closing_balance'];
            $transactions = $ledger['rows'];
        }

        return view('reports.bank-cash-book', [
            'bankAccounts' => $bankAccounts,
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function export(Request $request, string $report, string $format)
    {
        $company = current_company_or_fail();
        [$dateFrom, $dateTo, $financialYearId] = $this->resolveFilters($request);

        [$title, $headings, $rows] = match ($report) {
            'category-wise' => [
                'Category-wise Expense Report',
                ['Category', 'Count', 'Total Amount'],
                $this->reports->categoryWise($company->id, $dateFrom, $dateTo, $financialYearId)
                    ->map(fn ($row) => [$row->category_name, $row->count, (float) $row->total]),
            ],
            'vendor-wise' => [
                'Vendor-wise Expense Report',
                ['Vendor', 'Count', 'Total Amount'],
                $this->reports->vendorWise($company->id, $dateFrom, $dateTo, $financialYearId)
                    ->map(fn ($row) => [$row->vendor_name, $row->count, (float) $row->total]),
            ],
            'payment-wise' => [
                'Payment-wise Expense Report',
                ['Payment Method', 'Count', 'Total Amount'],
                $this->reports->paymentMethodWise($company->id, $dateFrom, $dateTo, $financialYearId)
                    ->map(fn ($row) => [$row->payment_method_name, $row->count, (float) $row->total]),
            ],
            'financial-year-summary' => (function () use ($company, $request) {
                $financialYear = $this->resolveFinancialYear($request, $company->id);
                $data = $financialYear ? $this->reports->financialYearSummary($company->id, $financialYear) : ['rows' => collect()];

                return [
                    'Financial Year Expense Summary',
                    ['Category', 'Count', 'Total Amount', '% of Total'],
                    $data['rows']->map(fn ($row) => [$row->category_name, $row->count, (float) $row->total, $row->percentage.'%']),
                ];
            })(),
            default => abort(404),
        };

        $filename = Str::slug($title).'-'.now()->format('Y-m-d');

        $mimeTypes = [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
        ];

        return match ($format) {
            'xlsx', 'csv' => Excel::download(new ArrayExport($rows, $headings, $title), "{$filename}.{$format}", null, ['Content-Type' => $mimeTypes[$format]]),
            'pdf' => Pdf::loadView('reports.pdf.simple', compact('title', 'headings', 'rows'))->download("{$filename}.pdf"),
            default => abort(404),
        };
    }

    private function resolveFilters(Request $request): array
    {
        return [
            $request->filled('date_from') ? $request->string('date_from')->toString() : null,
            $request->filled('date_to') ? $request->string('date_to')->toString() : null,
            $request->filled('financial_year_id') ? $request->integer('financial_year_id') : null,
        ];
    }

    private function resolveFinancialYear(Request $request, int $companyId): ?FinancialYear
    {
        if ($request->filled('financial_year_id')) {
            return FinancialYear::where('company_id', $companyId)->find($request->integer('financial_year_id'));
        }

        return FinancialYear::where('company_id', $companyId)->where('is_active', true)->first();
    }

    private function financialYears()
    {
        return current_company()?->financialYears()->orderByDesc('start_date')->get() ?? collect();
    }
}

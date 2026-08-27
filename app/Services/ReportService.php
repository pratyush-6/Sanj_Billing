<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    private function baseQuery(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $financialYearId = null): Builder
    {
        return Expense::query()
            ->where('expenses.company_id', $companyId)
            ->where('expenses.status', '!=', 'Cancelled')
            ->when($financialYearId, fn ($query) => $query->where('expenses.financial_year_id', $financialYearId))
            ->when($dateFrom, fn ($query) => $query->whereDate('expenses.expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('expenses.expense_date', '<=', $dateTo));
    }

    public function categoryWise(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $financialYearId = null): Collection
    {
        return $this->baseQuery($companyId, $dateFrom, $dateTo, $financialYearId)
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->selectRaw('expense_categories.id as category_id, expense_categories.name as category_name, SUM(expenses.total_amount) as total, COUNT(*) as count')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => (object) $row->getAttributes());
    }

    public function vendorWise(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $financialYearId = null): Collection
    {
        return $this->baseQuery($companyId, $dateFrom, $dateTo, $financialYearId)
            ->leftJoin('vendors', 'vendors.id', '=', 'expenses.vendor_id')
            ->selectRaw("COALESCE(vendors.id, 0) as vendor_id, COALESCE(vendors.name, 'No Vendor') as vendor_name, SUM(expenses.total_amount) as total, COUNT(*) as count")
            ->groupBy('vendors.id', 'vendors.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => (object) $row->getAttributes());
    }

    public function paymentMethodWise(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $financialYearId = null): Collection
    {
        return $this->baseQuery($companyId, $dateFrom, $dateTo, $financialYearId)
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'expenses.payment_method_id')
            ->selectRaw("COALESCE(payment_methods.id, 0) as payment_method_id, COALESCE(payment_methods.name, 'Unspecified') as payment_method_name, SUM(expenses.total_amount) as total, COUNT(*) as count")
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => (object) $row->getAttributes());
    }

    public function bankAccountWise(int $companyId, ?string $dateFrom = null, ?string $dateTo = null, ?int $financialYearId = null): Collection
    {
        return $this->baseQuery($companyId, $dateFrom, $dateTo, $financialYearId)
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'expenses.bank_account_id')
            ->selectRaw("COALESCE(bank_accounts.id, 0) as bank_account_id, COALESCE(bank_accounts.account_name, 'Unspecified') as account_name, SUM(expenses.total_amount) as total, COUNT(*) as count")
            ->groupBy('bank_accounts.id', 'bank_accounts.account_name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => (object) $row->getAttributes());
    }

    public function monthlyComparison(int $companyId, FinancialYear $financialYear): array
    {
        $months = collect();
        $cursor = $financialYear->start_date->copy()->startOfMonth();
        $end = $financialYear->end_date->copy()->startOfMonth();

        while ($cursor <= $end) {
            $months->push($cursor->copy());
            $cursor->addMonth();
        }

        $rows = $this->baseQuery($companyId, null, null, $financialYear->id)
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->selectRaw("expense_categories.id as category_id, expense_categories.name as category_name, DATE_FORMAT(expenses.expense_date, '%Y-%m') as ym, SUM(expenses.total_amount) as total")
            ->groupBy('expense_categories.id', 'expense_categories.name', 'ym')
            ->get()
            ->map(fn ($row) => (object) $row->getAttributes());

        $grid = $rows->groupBy('category_name')->map(function ($group, $categoryName) use ($months) {
            $values = $months->mapWithKeys(function ($month) use ($group) {
                $match = $group->firstWhere('ym', $month->format('Y-m'));

                return [$month->format('Y-m') => (float) ($match->total ?? 0)];
            });

            return [
                'category' => $categoryName,
                'values' => $values,
                'total' => $values->sum(),
            ];
        })->sortByDesc('total')->values();

        return ['months' => $months, 'rows' => $grid];
    }

    public function financialYearSummary(int $companyId, FinancialYear $financialYear): array
    {
        $rows = $this->categoryWise($companyId, null, null, $financialYear->id);
        $grandTotal = (float) $rows->sum('total');

        $rows = $rows->map(fn ($row) => (object) [
            ...(array) $row,
            'percentage' => $grandTotal > 0 ? round(((float) $row->total / $grandTotal) * 100, 1) : 0,
        ]);

        return ['rows' => $rows, 'total' => $grandTotal];
    }

    public function dailySummary(int $companyId, string $date): array
    {
        $categories = $this->categoryWise($companyId, $date, $date);

        $expenses = $this->baseQuery($companyId, $date, $date)
            ->with(['category', 'vendor', 'paymentMethod'])
            ->orderBy('id')
            ->get();

        return [
            'date' => $date,
            'categories' => $categories,
            'expenses' => $expenses,
            'total' => (float) $categories->sum('total'),
        ];
    }

    public function monthlySummary(int $companyId, string $from, string $to): array
    {
        $categories = $this->categoryWise($companyId, $from, $to);

        return [
            'from' => $from,
            'to' => $to,
            'categories' => $categories,
            'total' => (float) $categories->sum('total'),
        ];
    }

    public function monthlyTrend(int $companyId, int $months = 6): Collection
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $rows = $this->baseQuery($companyId, $start->toDateString(), now()->toDateString())
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as ym, SUM(total_amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return collect(range(0, $months - 1))->map(function ($i) use ($start, $rows) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            return (object) [
                'label' => $month->format('M Y'),
                'total' => (float) ($rows[$key] ?? 0),
            ];
        });
    }
}

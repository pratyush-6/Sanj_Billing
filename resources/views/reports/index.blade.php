<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Reports" description="Expense reports across categories, vendors, payments and time." />
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @php
        $reports = [
            ['route' => 'reports.daily', 'title' => 'Daily Expense', 'description' => 'Totals and breakdown for a single day.'],
            ['route' => 'reports.monthly', 'title' => 'Monthly Expense', 'description' => 'Totals and breakdown for a month.'],
            ['route' => 'reports.category-wise', 'title' => 'Category-wise', 'description' => 'Spending grouped by expense category.'],
            ['route' => 'reports.vendor-wise', 'title' => 'Vendor-wise', 'description' => 'Spending grouped by vendor/supplier.'],
            ['route' => 'reports.payment-wise', 'title' => 'Payment-wise', 'description' => 'Spending by payment method and account.'],
            ['route' => 'reports.bank-cash-book', 'title' => 'Cash & Bank Book', 'description' => 'Running balance per account.'],
            ['route' => 'reports.monthly-comparison', 'title' => 'Monthly Comparison', 'description' => 'Category totals across months, with charts.'],
            ['route' => 'reports.financial-year-summary', 'title' => 'Financial Year Summary', 'description' => 'Full-year category totals for a financial year.'],
        ];
        @endphp

        @foreach ($reports as $report)
            <a href="{{ route($report['route']) }}">
                <x-ui.card class="h-full hover:border-brand-300 hover:shadow-md transition">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">{{ $report['title'] }}</p>
                    <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ $report['description'] }}</p>
                </x-ui.card>
            </a>
        @endforeach
    </div>
</x-app-layout>

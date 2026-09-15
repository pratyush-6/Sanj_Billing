<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Dashboard" description="{{ now()->format('l, d M Y') }}" />
    </x-slot>

    <div class="space-y-6">

        @if (! $company)
            @can('companies.manage')
                <x-ui.alert variant="warning" title="Welcome to Sanjeevani. Let's set things up.">
                    Start by creating your company profile.
                    <a href="{{ route('companies.create') }}" class="inline-block mt-3 text-sm font-semibold text-amber-800 dark:text-amber-300 underline">
                        Set Up Company &rarr;
                    </a>
                </x-ui.alert>
            @else
                <x-ui.alert variant="warning" title="No company access yet.">
                    You haven't been granted access to a company. Contact your administrator.
                </x-ui.alert>
            @endcan
        @elseif (! $activeFinancialYear)
            <x-ui.alert variant="warning" title="No active financial year.">
                Create and activate a financial year to begin recording transactions.
                @can('financial-years.manage')
                    <a href="{{ route('financial-years.create') }}" class="inline-block mt-3 text-sm font-semibold text-amber-800 dark:text-amber-300 underline">
                        Create Financial Year &rarr;
                    </a>
                @endcan
            </x-ui.alert>
        @else
            <x-ui.card>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-ink-400 dark:text-ink-500">Company</p>
                        <p class="text-lg font-bold text-ink-900 dark:text-ink-50">{{ $company->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium uppercase tracking-wide text-ink-400 dark:text-ink-500">Active Financial Year</p>
                        <p class="text-lg font-bold text-ink-900 dark:text-ink-50">{{ $activeFinancialYear->name }}</p>
                        <p class="text-xs text-ink-400 dark:text-ink-500">
                            {{ $activeFinancialYear->start_date->format('d-M-Y') }} &ndash; {{ $activeFinancialYear->end_date->format('d-M-Y') }}
                        </p>
                    </div>
                </div>
            </x-ui.card>

            @if ($stats)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.stat label="Today" accent="sky" :value="'₹'.number_format($stats['today'], 2)">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </x-slot>
                    </x-ui.stat>
                    <x-ui.stat label="This Month" accent="brand" :value="'₹'.number_format($stats['this_month'], 2)">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        </x-slot>
                    </x-ui.stat>
                    <x-ui.stat :label="$activeFinancialYear->name" accent="emerald" :value="'₹'.number_format($stats['financial_year'], 2)" hint="Total for the financial year">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941" /></svg>
                        </x-slot>
                    </x-ui.stat>
                </div>
            @endif

            @if ($financials)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-ui.stat label="Net Profit" :accent="$financials['net_profit'] >= 0 ? 'emerald' : 'rose'" :value="'₹'.number_format($financials['net_profit'], 2)" hint="Current financial year" />
                    <x-ui.stat label="Cash Balance" accent="sky" :value="'₹'.number_format($financials['cash_balance'], 2)" />
                    <x-ui.stat label="Bank Balance" accent="brand" :value="'₹'.number_format($financials['bank_balance'], 2)" />
                    <x-ui.stat label="Payable" accent="amber" :value="'₹'.number_format($financials['payable'], 2)" hint="GST + TDS + other liabilities" />
                </div>
            @endif

            @if ($charts)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-ui.card>
                        <p class="text-sm font-semibold text-ink-900 dark:text-ink-50 mb-3">Expense by Category</p>
                        @if ($charts['category']->isEmpty())
                            <x-ui.empty-state title="No expenses yet" />
                        @else
                            <div class="h-64">
                                <x-ui.chart type="doughnut" :data="[
                                    'labels' => $charts['category']->pluck('category_name'),
                                    'datasets' => [['data' => $charts['category']->pluck('total'), 'backgroundColor' => ['#0d9488','#0891b2','#6366f1','#d946ef','#f59e0b','#ef4444','#10b981','#3b82f6']]],
                                ]" />
                            </div>
                        @endif
                    </x-ui.card>

                    <x-ui.card>
                        <p class="text-sm font-semibold text-ink-900 dark:text-ink-50 mb-3">Monthly Trend</p>
                        <div class="h-64">
                            <x-ui.chart type="line" :data="[
                                'labels' => $charts['monthlyTrend']->pluck('label'),
                                'datasets' => [['label' => 'Expenses', 'data' => $charts['monthlyTrend']->pluck('total'), 'borderColor' => '#0d9488', 'backgroundColor' => 'rgba(13,148,136,0.1)', 'fill' => true, 'tension' => 0.3]],
                            ]" :options="['plugins' => ['legend' => ['display' => false]]]" />
                        </div>
                    </x-ui.card>

                    <x-ui.card>
                        <p class="text-sm font-semibold text-ink-900 dark:text-ink-50 mb-3">By Payment Method</p>
                        @if ($charts['payment']->isEmpty())
                            <x-ui.empty-state title="No expenses yet" />
                        @else
                            <div class="h-64">
                                <x-ui.chart type="doughnut" :data="[
                                    'labels' => $charts['payment']->pluck('payment_method_name'),
                                    'datasets' => [['data' => $charts['payment']->pluck('total'), 'backgroundColor' => ['#0d9488','#0891b2','#6366f1','#d946ef','#f59e0b','#ef4444','#10b981','#3b82f6']]],
                                ]" />
                            </div>
                        @endif
                    </x-ui.card>

                    <x-ui.card>
                        <p class="text-sm font-semibold text-ink-900 dark:text-ink-50 mb-3">Top Vendors</p>
                        @if ($charts['topVendors']->isEmpty())
                            <x-ui.empty-state title="No vendor expenses yet" />
                        @else
                            <div class="h-64">
                                <x-ui.chart type="bar" :data="[
                                    'labels' => $charts['topVendors']->pluck('vendor_name'),
                                    'datasets' => [['label' => 'Amount', 'data' => $charts['topVendors']->pluck('total'), 'backgroundColor' => '#0d9488']],
                                ]" :options="['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]]" />
                            </div>
                        @endif
                    </x-ui.card>
                </div>
            @endif

            @if ($lowStock && $lowStock->isNotEmpty())
                <x-ui.card>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Low Stock</p>
                        <a href="{{ route('inventory-reports.low-stock') }}" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">View all &rarr;</a>
                    </div>
                    <div class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($lowStock as $row)
                            <div class="flex items-center justify-between py-2 text-sm">
                                <span class="text-ink-700 dark:text-ink-200">{{ $row->product->name }}</span>
                                <span class="text-rose-600 dark:text-rose-400 font-medium">{{ number_format($row->current_stock, 2) }} / {{ number_format($row->product->min_stock_level, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif
        @endif

        <div>
            <h3 class="text-sm font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide mb-3">Modules</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @can('expenses.view')
                    <a href="{{ route('expenses.index') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 dark:border-ink-700/70 bg-white dark:bg-ink-900 p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 group-hover:bg-brand-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0116.5 0M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H2.25m17.25 0h-2.25" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800 dark:text-ink-100">Expenses</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500 mt-0.5">Record & search expenses</p>
                        </div>
                    </a>
                @endcan
                @can('vendors.manage')
                    <a href="{{ route('vendors.index') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 dark:border-ink-700/70 bg-white dark:bg-ink-900 p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 group-hover:bg-sky-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800 dark:text-ink-100">Vendors</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500 mt-0.5">Manage suppliers</p>
                        </div>
                    </a>
                @endcan
                @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 dark:border-ink-700/70 bg-white dark:bg-ink-900 p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800 dark:text-ink-100">Reports</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500 mt-0.5">Expense reports & exports</p>
                        </div>
                    </a>
                @endcan
                @can('accounting.view')
                    <a href="{{ route('accounting.chart-of-accounts') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 dark:border-ink-700/70 bg-white dark:bg-ink-900 p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:bg-amber-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800 dark:text-ink-100">Accounting</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500 mt-0.5">Ledger, trial balance, P&amp;L, balance sheet</p>
                        </div>
                    </a>
                @endcan
                @can('inventory.view')
                    <a href="{{ route('products.index') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 dark:border-ink-700/70 bg-white dark:bg-ink-900 p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 group-hover:bg-teal-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800 dark:text-ink-100">Inventory</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500 mt-0.5">Products, stock levels & adjustments</p>
                        </div>
                    </a>
                @endcan
                @can('inventory.view')
                    <a href="{{ route('vendor-quotations.index') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 dark:border-ink-700/70 bg-white dark:bg-ink-900 p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800 dark:text-ink-100">Procurement</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500 mt-0.5">Quotations, purchase orders & receipts</p>
                        </div>
                    </a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>

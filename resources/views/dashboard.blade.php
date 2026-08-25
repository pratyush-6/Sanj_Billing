<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Dashboard" description="{{ now()->format('l, d M Y') }}" />
    </x-slot>

    <div class="space-y-6">

        @if (! $company)
            <x-ui.alert variant="warning" title="Welcome to Sanjeevani. Let's set things up.">
                Start by creating your company profile.
                @can('companies.manage')
                    <a href="{{ route('company.edit') }}" class="inline-block mt-3 text-sm font-semibold text-amber-800 underline">
                        Set Up Company &rarr;
                    </a>
                @endcan
            </x-ui.alert>
        @elseif (! $activeFinancialYear)
            <x-ui.alert variant="warning" title="No active financial year.">
                Create and activate a financial year to begin recording transactions.
                @can('financial-years.manage')
                    <a href="{{ route('financial-years.create') }}" class="inline-block mt-3 text-sm font-semibold text-amber-800 underline">
                        Create Financial Year &rarr;
                    </a>
                @endcan
            </x-ui.alert>
        @else
            <x-ui.card>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-ink-400">Company</p>
                        <p class="text-lg font-bold text-ink-900">{{ $company->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium uppercase tracking-wide text-ink-400">Active Financial Year</p>
                        <p class="text-lg font-bold text-ink-900">{{ $activeFinancialYear->name }}</p>
                        <p class="text-xs text-ink-400">
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
        @endif

        <div>
            <h3 class="text-sm font-semibold text-ink-500 uppercase tracking-wide mb-3">Modules</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @can('expenses.view')
                    <a href="{{ route('expenses.index') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 bg-white p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 group-hover:bg-brand-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0116.5 0M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H2.25m17.25 0h-2.25" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800">Expenses</p>
                            <p class="text-xs text-ink-400 mt-0.5">Record & search expenses</p>
                        </div>
                    </a>
                @endcan
                @can('vendors.manage')
                    <a href="{{ route('vendors.index') }}" class="group flex items-start gap-3 rounded-xl border border-ink-200/70 bg-white p-4 hover:border-brand-300 hover:shadow-sm transition">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600 group-hover:bg-sky-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-ink-800">Vendors</p>
                            <p class="text-xs text-ink-400 mt-0.5">Manage suppliers</p>
                        </div>
                    </a>
                @endcan
                <div class="flex items-start gap-3 rounded-xl border border-dashed border-ink-200 p-4 opacity-60">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-100 text-ink-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-ink-800">Reports</p>
                        <p class="text-xs text-ink-400 mt-0.5">Coming in Phase 3</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-dashed border-ink-200 p-4 opacity-60">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-100 text-ink-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-ink-800">Accounting</p>
                        <p class="text-xs text-ink-400 mt-0.5">Coming in Phase 4</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

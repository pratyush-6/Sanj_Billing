<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (! $company)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-6">
                    <p class="font-medium">Welcome to Sanjeevani. Let's set things up.</p>
                    <p class="mt-1 text-sm">Start by creating your company profile.</p>
                    @can('companies.manage')
                        <a href="{{ route('company.edit') }}" class="inline-block mt-4 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-md hover:bg-amber-700">
                            Set Up Company
                        </a>
                    @endcan
                </div>
            @elseif (! $activeFinancialYear)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-6">
                    <p class="font-medium">No active financial year.</p>
                    <p class="mt-1 text-sm">Create and activate a financial year to begin recording transactions.</p>
                    @can('financial-years.manage')
                        <a href="{{ route('financial-years.create') }}" class="inline-block mt-4 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-md hover:bg-amber-700">
                            Create Financial Year
                        </a>
                    @endcan
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Company</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $company->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Active Financial Year</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $activeFinancialYear->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $activeFinancialYear->start_date->format('d-M-Y') }} to {{ $activeFinancialYear->end_date->format('d-M-Y') }}
                            </p>
                        </div>
                    </div>

                    @if ($stats)
                        <div class="grid grid-cols-3 gap-4 border-t pt-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Today</p>
                                <p class="text-xl font-semibold text-gray-900">₹{{ number_format($stats['today'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">This Month</p>
                                <p class="text-xl font-semibold text-gray-900">₹{{ number_format($stats['this_month'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ $activeFinancialYear->name }}</p>
                                <p class="text-xl font-semibold text-gray-900">₹{{ number_format($stats['financial_year'], 2) }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Modules</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @can('expenses.view')
                        <a href="{{ route('expenses.index') }}" class="border rounded-lg p-4 hover:border-indigo-400 hover:shadow-sm transition">
                            <p class="font-medium text-gray-800">Expenses</p>
                            <p class="text-xs text-gray-500 mt-1">Record & search expenses</p>
                        </a>
                    @endcan
                    @can('vendors.manage')
                        <a href="{{ route('vendors.index') }}" class="border rounded-lg p-4 hover:border-indigo-400 hover:shadow-sm transition">
                            <p class="font-medium text-gray-800">Vendors</p>
                            <p class="text-xs text-gray-500 mt-1">Manage suppliers</p>
                        </a>
                    @endcan
                    <div class="border rounded-lg p-4 opacity-60">
                        <p class="font-medium text-gray-800">Reports</p>
                        <p class="text-xs text-gray-500 mt-1">Coming in Phase 3</p>
                    </div>
                    <div class="border rounded-lg p-4 opacity-60">
                        <p class="font-medium text-gray-800">Accounting</p>
                        <p class="text-xs text-gray-500 mt-1">Coming in Phase 4</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

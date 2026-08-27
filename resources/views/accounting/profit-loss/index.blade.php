<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Profit &amp; Loss" :description="$financialYear?->name ?? 'Select a financial year'" />
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <x-ui.card>
            <form method="GET" class="flex items-end gap-3 text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Financial Year</label>
                    <select name="financial_year_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($financialYears as $fy)
                            <option value="{{ $fy->id }}" @selected(($financialYear?->id ?? request('financial_year_id')) == $fy->id)>{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="submit">View</x-secondary-button>
            </form>
        </x-ui.card>

        @if (! $data)
            <x-ui.card>
                <x-ui.empty-state title="No financial year" description="Select a financial year to view its Profit & Loss statement." />
            </x-ui.card>
        @else
            <x-ui.card :padded="false">
                <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Income</p>
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @forelse ($data['income_accounts'] as $row)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">{{ $row->name }}</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-2.5 text-sm text-ink-400 dark:text-ink-500">No income recorded yet.</div>
                    @endforelse
                    <div class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold bg-ink-50/40 dark:bg-ink-800/40">
                        <span class="text-ink-900 dark:text-ink-50">Total Income</span>
                        <span class="text-ink-900 dark:text-ink-50">{{ number_format($data['total_income'], 2) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Expenses</p>
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @forelse ($data['expenses_by_nature'] as $nature => $amount)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">{{ $nature }}</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-2.5 text-sm text-ink-400 dark:text-ink-500">No expenses recorded yet.</div>
                    @endforelse
                    <div class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold bg-ink-50/40 dark:bg-ink-800/40">
                        <span class="text-ink-900 dark:text-ink-50">Total Expenses</span>
                        <span class="text-ink-900 dark:text-ink-50">{{ number_format($data['total_expense'], 2) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Profit Before Tax</p>
                    <p class="text-lg font-bold {{ $data['profit_before_tax'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ number_format($data['profit_before_tax'], 2) }}
                    </p>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

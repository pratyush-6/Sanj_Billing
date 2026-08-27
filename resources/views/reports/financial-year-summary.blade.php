<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Financial Year Summary" :description="$financialYear?->name ?? 'Select a financial year'">
            <x-slot name="actions">
                @if ($financialYear)
                    @include('reports._export-buttons', ['report' => 'financial-year-summary'])
                @endif
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4">
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

        @if (! $financialYear || $rows->isEmpty())
            <x-ui.card>
                <x-ui.empty-state title="No expenses found" description="Try a different financial year." />
            </x-ui.card>
        @else
            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Category</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Total Amount</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">% of Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($rows as $row)
                                <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                    <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $row->category_name }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right text-ink-900 dark:text-ink-50">{{ number_format($row->total, 2) }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right text-ink-500 dark:text-ink-400">{{ $row->percentage }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-ink-50/60 dark:bg-ink-800/60 font-semibold">
                                <td class="px-4 py-3 text-sm text-ink-900 dark:text-ink-50">Total Expenses</td>
                                <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">{{ number_format($total, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

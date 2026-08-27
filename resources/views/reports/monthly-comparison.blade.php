<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Monthly Comparison" description="Category totals across the months of a financial year." />
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
            <x-ui.card>
                <div class="h-80">
                    <x-ui.chart type="bar" :data="[
                        'labels' => $months->map(fn ($m) => $m->format('M Y')),
                        'datasets' => $rows->take(6)->values()->map(fn ($row, $i) => [
                            'label' => $row['category'],
                            'data' => $row['values']->values(),
                            'backgroundColor' => ['#0d9488','#0891b2','#6366f1','#d946ef','#f59e0b','#ef4444'][$i] ?? '#94a3b8',
                        ])->all(),
                    ]" />
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800 text-sm">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Category</th>
                                @foreach ($months as $month)
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ $month->format('M Y') }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($rows as $row)
                                <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                    <td class="px-4 py-3.5 font-medium text-ink-900 dark:text-ink-50 whitespace-nowrap">{{ $row['category'] }}</td>
                                    @foreach ($row['values'] as $value)
                                        <td class="px-4 py-3.5 text-right text-ink-600 dark:text-ink-300">{{ $value > 0 ? number_format($value, 0) : '—' }}</td>
                                    @endforeach
                                    <td class="px-4 py-3.5 text-right font-semibold text-ink-900 dark:text-ink-50">{{ number_format($row['total'], 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

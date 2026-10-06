<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Branch-wise Expense Report" description="Expenses split across every branch of the company. Unassigned covers expenses recorded before branches existed." />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            @include('reports._filters', ['financialYears' => $financialYears])
        </x-ui.card>

        @if ($rows->isEmpty())
            <x-ui.card>
                <x-ui.empty-state title="No expenses found" description="Try adjusting the date range or financial year." />
            </x-ui.card>
        @else
            <x-ui.card>
                <div class="h-72">
                    <x-ui.chart type="bar" :data="[
                        'labels' => $rows->pluck('branch_name'),
                        'datasets' => [['label' => 'Amount', 'data' => $rows->pluck('total'), 'backgroundColor' => '#0d9488']],
                    ]" :options="['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]]" />
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Branch</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Count</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($rows as $row)
                                <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                    <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $row->branch_name }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ $row->count }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-ink-50/60 dark:bg-ink-800/60 font-semibold">
                                <td class="px-4 py-3 text-sm text-ink-900 dark:text-ink-50">Total</td>
                                <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">{{ $rows->sum('count') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">{{ number_format($total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

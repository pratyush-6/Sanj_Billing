<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Vendor-wise Expense Report" description="Spending grouped by vendor/supplier.">
            <x-slot name="actions">
                @include('reports._export-buttons', ['report' => 'vendor-wise'])
            </x-slot>
        </x-ui.page-header>
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
                        'labels' => $rows->take(10)->pluck('vendor_name'),
                        'datasets' => [['label' => 'Amount', 'data' => $rows->take(10)->pluck('total'), 'backgroundColor' => '#0d9488']],
                    ]" :options="['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]]" />
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Vendor</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Count</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($rows as $row)
                                <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                    <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">
                                        @if ($row->vendor_id)
                                            <a href="{{ route('parties.show', $row->vendor_id) }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $row->vendor_name }}</a>
                                        @else
                                            {{ $row->vendor_name }}
                                        @endif
                                    </td>
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

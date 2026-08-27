<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Monthly Expense Report" :description="\Carbon\Carbon::parse($from)->format('F Y')" />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="flex items-end gap-3 text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Month</label>
                    <input type="month" name="month" value="{{ $month }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <x-secondary-button type="submit">View</x-secondary-button>
            </form>
        </x-ui.card>

        <x-ui.stat label="Total for the month" :value="'₹'.number_format($total, 2)" accent="brand" />

        @if ($categories->isEmpty())
            <x-ui.card>
                <x-ui.empty-state title="No expenses recorded" description="Nothing was recorded in this month." />
            </x-ui.card>
        @else
            <x-ui.card>
                <div class="h-72">
                    <x-ui.chart type="bar" :data="[
                        'labels' => $categories->pluck('category_name'),
                        'datasets' => [['label' => 'Amount', 'data' => $categories->pluck('total'), 'backgroundColor' => '#0d9488']],
                    ]" :options="['plugins' => ['legend' => ['display' => false]]]" />
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($categories as $row)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">{{ $row->category_name }}</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

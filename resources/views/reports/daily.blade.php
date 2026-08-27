<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Daily Expense Report" :description="\Carbon\Carbon::parse($date)->format('l, d M Y')" />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="flex items-end gap-3 text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <x-secondary-button type="submit">View</x-secondary-button>
            </form>
        </x-ui.card>

        <x-ui.stat label="Total for the day" :value="'₹'.number_format($total, 2)" accent="brand" />

        @if ($expenses->isEmpty())
            <x-ui.card>
                <x-ui.empty-state title="No expenses recorded" description="Nothing was recorded on this date." />
            </x-ui.card>
        @else
            <x-ui.card :padded="false">
                <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">By Category</p>
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($categories as $row)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">{{ $row->category_name }}</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Expense #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Vendor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Payment</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($expenses as $expense)
                                <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                    <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $expense->expense_number }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->category->name }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->vendor?->name ?? '—' }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->paymentMethod?->name }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($expense->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

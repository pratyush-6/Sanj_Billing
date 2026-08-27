<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Journal" description="Every accounting transaction, auto-generated from your expense entries." />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="flex items-end gap-3 text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Financial Year</label>
                    <select name="financial_year_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($financialYears as $fy)
                            <option value="{{ $fy->id }}" @selected($selectedFinancialYearId == $fy->id)>{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="submit">Filter</x-secondary-button>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Entry #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Narration</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($entries as $entry)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60 cursor-pointer" onclick="window.location='{{ route('accounting.journal.show', $entry) }}'">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $entry->entry_number }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300 whitespace-nowrap">{{ $entry->entry_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $entry->narration }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($entry->items->sum('debit'), 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$entry->status === 'Posted' ? 'success' : 'neutral'">{{ $entry->status }}</x-ui.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No journal entries yet" description="Journal entries are created automatically when you record expenses." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $entries->links() }}</div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

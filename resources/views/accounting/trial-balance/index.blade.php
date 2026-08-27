<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Trial Balance" :description="'As of '.\Carbon\Carbon::parse($asOfDate)->format('d-M-Y')" />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="flex items-end gap-3 text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">As of</label>
                    <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <x-secondary-button type="submit">View</x-secondary-button>
            </form>
        </x-ui.card>

        <x-ui.alert :variant="abs($totalDebit - $totalCredit) < 0.01 ? 'success' : 'danger'">
            @if (abs($totalDebit - $totalCredit) < 0.01)
                Balanced &mdash; total debits equal total credits.
            @else
                Out of balance by {{ number_format(abs($totalDebit - $totalCredit), 2) }}.
            @endif
        </x-ui.alert>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Account</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $row->account->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-500 dark:text-ink-400">{{ $row->account->type }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-900 dark:text-ink-50">{{ $row->debit > 0 ? number_format($row->debit, 2) : '—' }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-900 dark:text-ink-50">{{ $row->credit > 0 ? number_format($row->credit, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-ui.empty-state title="No activity yet" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-ink-50/60 dark:bg-ink-800/60 font-semibold">
                            <td class="px-4 py-3 text-sm text-ink-900 dark:text-ink-50" colspan="2">Total</td>
                            <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">{{ number_format($totalDebit, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

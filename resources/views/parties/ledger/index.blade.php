<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Party Ledger" description="Outstanding balances derived from posted sale invoices, purchase bills and payments." />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Party</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Receivable</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Payable</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Net Balance</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($balances as $row)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $row['party']->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($row['receivable'], 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($row['payable'], 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-semibold {{ $row['balance'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($row['balance'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-ink-600 dark:text-ink-300') }}">
                                    {{ $row['balance'] > 0 ? 'You will get ' : ($row['balance'] < 0 ? 'You will give ' : '') }}{{ number_format(abs($row['balance']), 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm">
                                    <a href="{{ route('parties.ledger', $row['party']) }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">Statement</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No party activity yet" description="Balances appear once a sale invoice or purchase bill is posted." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

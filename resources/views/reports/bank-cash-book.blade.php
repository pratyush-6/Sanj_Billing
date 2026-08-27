<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Cash & Bank Book" description="Running balance per account, based on recorded expenses." />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Account</label>
                    <select name="bank_account_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($bankAccounts as $account)
                            <option value="{{ $account->id }}" @selected($bankAccount?->id === $account->id)>{{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <x-secondary-button type="submit">View</x-secondary-button>
            </form>
        </x-ui.card>

        @if (! $bankAccount)
            <x-ui.card>
                <x-ui.empty-state title="No accounts yet" description="Add a bank or cash account first." />
            </x-ui.card>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.stat label="Opening Balance" :value="'₹'.number_format($openingBalance, 2)" accent="sky" />
                <x-ui.stat label="Closing Balance" :value="'₹'.number_format($closingBalance, 2)" :accent="$closingBalance < 0 ? 'rose' : 'emerald'" />
            </div>

            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Particulars</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Reference</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Deposit</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Withdrawal</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @forelse ($transactions as $row)
                                <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                    <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300 whitespace-nowrap">{{ $row->date->format('d-M-Y') }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $row->particulars }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-500 dark:text-ink-400">{{ $row->reference }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right text-emerald-600 dark:text-emerald-400">{{ $row->debit > 0 ? number_format($row->debit, 2) : '—' }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right text-rose-600 dark:text-rose-400">{{ $row->credit > 0 ? number_format($row->credit, 2) : '—' }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-ui.empty-state title="No transactions in this range" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

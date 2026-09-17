<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$party->name.' — Ledger'">
            <x-slot name="actions">
                <a href="{{ route('parties.show', $party) }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Party</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4">
        @php
            $finalBalance = $statement->last()['running_balance'] ?? 0;
        @endphp

        <x-ui.card>
            <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Current Balance</div>
            <div class="text-2xl font-semibold mt-1 {{ $finalBalance > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($finalBalance < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-ink-900 dark:text-ink-50') }}">
                {{ $finalBalance > 0 ? 'You will get ' : ($finalBalance < 0 ? 'You will give ' : '') }}{{ number_format(abs($finalBalance), 2) }}
            </div>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-700 dark:text-ink-200">Statement</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Reference</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($statement as $row)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $row['type'] }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    @if ($row['route'])
                                        <a href="{{ $row['route'] }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $row['reference'] }}</a>
                                    @else
                                        {{ $row['reference'] }}
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-sm text-right {{ $row['amount'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ number_format($row['amount'], 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($row['running_balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="No posted transactions yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-700 dark:text-ink-200">Item-wise Transaction History</h3>
            </div>
            <div class="divide-y divide-ink-100 dark:divide-ink-800">
                @forelse ($itemHistory as $productId => $rows)
                    <div class="px-4 py-3">
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mb-2">{{ $rows->first()['product']->name }} <span class="text-ink-400 dark:text-ink-500 font-normal">({{ $rows->first()['product']->sku }})</span></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide py-1 pr-4">Date</th>
                                        <th class="text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide py-1 pr-4">Type</th>
                                        <th class="text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide py-1 pr-4">Reference</th>
                                        <th class="text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide py-1 pr-4">Qty</th>
                                        <th class="text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide py-1">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr>
                                            <td class="py-1 pr-4 text-ink-600 dark:text-ink-300">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                                            <td class="py-1 pr-4">
                                                <x-ui.badge :variant="$row['direction'] === 'Out' ? 'brand' : 'info'">{{ $row['type'] }}</x-ui.badge>
                                            </td>
                                            <td class="py-1 pr-4"><a href="{{ $row['route'] }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $row['reference'] }}</a></td>
                                            <td class="py-1 pr-4 text-right text-ink-600 dark:text-ink-300">{{ number_format($row['quantity'], 2) }}</td>
                                            <td class="py-1 text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-3">
                        <x-ui.empty-state title="No product transactions yet" />
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

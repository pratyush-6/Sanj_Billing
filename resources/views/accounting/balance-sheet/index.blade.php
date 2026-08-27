<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Balance Sheet" :description="'As of '.\Carbon\Carbon::parse($asOfDate)->format('d-M-Y')" />
    </x-slot>

    <div class="max-w-4xl space-y-4">
        <x-ui.card>
            <form method="GET" class="flex items-end gap-3 text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">As of</label>
                    <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <x-secondary-button type="submit">View</x-secondary-button>
            </form>
        </x-ui.card>

        <x-ui.alert :variant="$data['balanced'] ? 'success' : 'danger'">
            @if ($data['balanced'])
                Balanced &mdash; assets equal liabilities plus equity.
            @else
                Out of balance by {{ number_format(abs($data['total_assets'] - $data['total_liabilities_and_equity']), 2) }}.
            @endif
        </x-ui.alert>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-ui.card :padded="false">
                <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Assets</p>
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @forelse ($data['assets'] as $row)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">{{ $row->name }}</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-2.5 text-sm text-ink-400 dark:text-ink-500">No assets recorded yet.</div>
                    @endforelse
                    <div class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold bg-ink-50/40 dark:bg-ink-800/40">
                        <span class="text-ink-900 dark:text-ink-50">Total Assets</span>
                        <span class="text-ink-900 dark:text-ink-50">{{ number_format($data['total_assets'], 2) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <div class="space-y-4">
                <x-ui.card :padded="false">
                    <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                        <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Liabilities</p>
                    </div>
                    <div class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($data['liabilities'] as $row)
                            <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                                <span class="text-ink-700 dark:text-ink-200">{{ $row->name }}</span>
                                <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->amount, 2) }}</span>
                            </div>
                        @empty
                            <div class="px-4 py-2.5 text-sm text-ink-400 dark:text-ink-500">No liabilities recorded yet.</div>
                        @endforelse
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold bg-ink-50/40 dark:bg-ink-800/40">
                            <span class="text-ink-900 dark:text-ink-50">Total Liabilities</span>
                            <span class="text-ink-900 dark:text-ink-50">{{ number_format($data['total_liabilities'], 2) }}</span>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card :padded="false">
                    <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                        <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Equity</p>
                    </div>
                    <div class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($data['equity'] as $row)
                            <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                                <span class="text-ink-700 dark:text-ink-200">{{ $row->name }}</span>
                                <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->amount, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">Retained Earnings</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($data['retained_earnings'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">Current Year Profit</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ number_format($data['current_year_profit'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold bg-ink-50/40 dark:bg-ink-800/40">
                            <span class="text-ink-900 dark:text-ink-50">Total Equity</span>
                            <span class="text-ink-900 dark:text-ink-50">{{ number_format($data['total_equity'], 2) }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>

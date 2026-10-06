<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Stock Transfer '.$transfer->transfer_number">
            <x-slot name="actions">
                <a href="{{ route('stock-transfers.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Transfers</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">From</div>
                    <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $transfer->fromBranch->name }}</div>
                </div>
                <div>
                    <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">To</div>
                    <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $transfer->toBranch->name }}</div>
                </div>
                <div>
                    <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Date</div>
                    <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $transfer->transfer_date->format('d-M-Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created by</div>
                    <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $transfer->creator?->name ?? '—' }}</div>
                </div>
            </div>

            @if ($transfer->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $transfer->notes }}</p>
            @endif
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Unit cost</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($transfer->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">{{ $item->product->displayLabel() }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($item->total_cost, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Stock Movements" description="Every stock-in and stock-out transaction, newest first." />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Product</label>
                    <select name="product_id" onchange="this.form.submit()" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <x-secondary-button type="submit">Filter</x-secondary-button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Direction</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($movements as $movement)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $movement->movement_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $movement->product->name }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$movement->direction === 'In' ? 'success' : 'warning'">{{ $movement->direction }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($movement->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    @if ($movement->source_type === \App\Models\GoodsReceipt::class)
                                        <a href="{{ route('goods-receipts.show', $movement->source_id) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $movement->reference_number }}</a>
                                    @elseif ($movement->source_type === \App\Models\StockAdjustment::class)
                                        <a href="{{ route('stock-adjustments.show', $movement->source_id) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $movement->reference_number }}</a>
                                    @else
                                        {{ $movement->reference_number ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $movement->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui.empty-state title="No stock movements yet" description="Movements appear here once goods are received or a stock adjustment is approved." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $movements->links() }}</div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

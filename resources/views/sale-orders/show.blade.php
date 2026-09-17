<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Sale Order '.$order->order_number">
            <x-slot name="actions">
                <a href="{{ route('sale-orders.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Sale Orders</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Customer</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $order->party->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Order Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $order->order_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created By</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $order->creator?->name ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="match($order->status) {
                    'Confirmed' => 'info',
                    'Converted' => 'brand',
                    'Cancelled' => 'danger',
                    default => 'neutral',
                }" class="text-sm">{{ $order->status }}</x-ui.badge>
            </div>

            @if ($order->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $order->notes }}</p>
            @endif

            @can('sale-orders.manage')
                <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                    @if ($order->status === 'Draft')
                        <a href="{{ route('sale-orders.edit', $order) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('sale-orders.confirm', $order) }}">
                            @csrf
                            <x-primary-button type="submit">Confirm Order</x-primary-button>
                        </form>
                    @endif
                    @if (in_array($order->status, ['Draft', 'Confirmed']))
                        <form method="POST" action="{{ route('sale-orders.cancel', $order) }}" onsubmit="return confirm('Cancel this sale order?');">
                            @csrf
                            <x-danger-button type="submit">Cancel</x-danger-button>
                        </form>
                    @endif
                </div>
            @endcan
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100">Line Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">{{ $item->product->name }} ({{ $item->product->sku }})</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($item->amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $item->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-ink-200 dark:border-ink-700">
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Total</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50">{{ number_format($order->totalAmount(), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

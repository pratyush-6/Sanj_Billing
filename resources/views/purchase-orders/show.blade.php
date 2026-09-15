<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Purchase Order '.$purchaseOrder->po_number">
            <x-slot name="actions">
                <a href="{{ route('purchase-orders.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Purchase Orders</a>
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

        @if ($purchaseOrder->quotation)
            <x-ui.alert variant="info">
                Converted from quotation
                <a href="{{ route('vendor-quotations.show', $purchaseOrder->quotation) }}" class="underline font-medium">{{ $purchaseOrder->quotation->quotation_number }}</a>.
            </x-ui.alert>
        @endif

        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Vendor</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $purchaseOrder->vendor->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">PO Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $purchaseOrder->po_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Expected Delivery</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $purchaseOrder->expected_delivery_date?->format('d-M-Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created By</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $purchaseOrder->creator?->name ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="match($purchaseOrder->status) {
                    'Sent' => 'info',
                    'Partially Received' => 'warning',
                    'Received' => 'success',
                    'Closed' => 'brand',
                    'Cancelled' => 'danger',
                    default => 'neutral',
                }" class="text-sm">{{ $purchaseOrder->status }}</x-ui.badge>
            </div>

            @if ($purchaseOrder->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $purchaseOrder->notes }}</p>
            @endif

            @can('purchase-orders.manage')
                <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                    @if ($purchaseOrder->status === 'Draft')
                        <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}" onsubmit="return confirm('Mark this purchase order as sent to the vendor? It cannot be edited after that.');">
                            @csrf
                            <x-primary-button type="submit">Send to Vendor</x-primary-button>
                        </form>
                    @endif
                    @if (in_array($purchaseOrder->status, ['Draft', 'Sent']))
                        <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this purchase order?');">
                            @csrf
                            <x-danger-button type="submit">Cancel</x-danger-button>
                        </form>
                    @endif
                    @can('goods-receipts.manage')
                        @if (in_array($purchaseOrder->status, ['Sent', 'Partially Received']))
                            <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $purchaseOrder->id]) }}">
                                <x-secondary-button>Receive Goods</x-secondary-button>
                            </a>
                        @endif
                    @endcan
                </div>
            @endcan

            @if ($purchaseOrder->goodsReceipts->isNotEmpty())
                <div class="border-t border-ink-100 dark:border-ink-800 mt-4 pt-4">
                    <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide mb-1">Goods Receipts</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($purchaseOrder->goodsReceipts as $grn)
                            <a href="{{ route('goods-receipts.show', $grn) }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">{{ $grn->grn_number }} ({{ $grn->status }})</a>
                        @endforeach
                    </div>
                </div>
            @endif

            @can('expenses.view')
                @if ($purchaseOrder->expenses->isNotEmpty())
                    <div class="border-t border-ink-100 dark:border-ink-800 mt-4 pt-4">
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide mb-1">Linked Expenses</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($purchaseOrder->expenses as $expense)
                                <a href="{{ route('expenses.edit', $expense) }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">{{ $expense->expense_number }} ({{ number_format($expense->total_amount, 2) }})</a>
                            @endforeach
                        </div>
                    </div>
                @endif
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
                        @foreach ($purchaseOrder->items as $item)
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
                            <td class="px-4 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50">{{ number_format($purchaseOrder->totalAmount(), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

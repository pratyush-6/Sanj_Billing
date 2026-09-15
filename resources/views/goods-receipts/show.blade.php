<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Goods Receipt '.$goodsReceipt->grn_number">
            <x-slot name="actions">
                <a href="{{ route('goods-receipts.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Goods Receipts</a>
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
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Purchase Order</div>
                        <div class="text-sm font-medium mt-1">
                            <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $goodsReceipt->purchaseOrder->po_number }}</a>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Vendor</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $goodsReceipt->purchaseOrder->vendor->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Receipt Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $goodsReceipt->receipt_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created By</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $goodsReceipt->creator?->name ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="$goodsReceipt->status === 'Completed' ? 'success' : 'neutral'" class="text-sm">{{ $goodsReceipt->status }}</x-ui.badge>
            </div>

            @if ($goodsReceipt->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $goodsReceipt->notes }}</p>
            @endif

            @can('goods-receipts.manage')
                @if ($goodsReceipt->status === 'Draft')
                    <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                        <a href="{{ route('goods-receipts.edit', $goodsReceipt) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('goods-receipts.complete', $goodsReceipt) }}" onsubmit="return confirm('Complete this receipt? Stock will be updated and this cannot be edited afterwards.');">
                            @csrf
                            <x-primary-button type="submit">Complete Receipt</x-primary-button>
                        </form>
                    </div>
                @endif
            @endcan
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100">Received Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty Received</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($goodsReceipt->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">{{ $item->purchaseOrderItem->product->name }} ({{ $item->purchaseOrderItem->product->sku }})</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($item->quantity_received, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $item->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

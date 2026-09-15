<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$goodsReceipt ? 'Edit Goods Receipt '.$goodsReceipt->grn_number : 'New Goods Receipt'" />
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @if (! $purchaseOrder)
            <x-ui.card>
                <form method="GET" action="{{ route('goods-receipts.create') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[280px]">
                        <x-input-label for="purchase_order_id" value="Select a Purchase Order to receive against *" />
                        <select id="purchase_order_id" name="purchase_order_id" onchange="this.form.submit()" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select a purchase order</option>
                            @foreach ($eligiblePurchaseOrders as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} — {{ $po->vendor->name }} ({{ $po->status }})</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                @if ($eligiblePurchaseOrders->isEmpty())
                    <x-ui.empty-state title="No purchase orders ready for receipt" description="Send a purchase order to a vendor before recording a goods receipt against it." />
                @endif
            </x-ui.card>
        @else
            @php
                $existingByPoItem = $goodsReceipt ? $goodsReceipt->items->keyBy('purchase_order_item_id') : collect();
                $rows = $purchaseOrder->items->filter(fn ($item) => ($remainingByItem[$item->id] ?? 0) > 0 || $existingByPoItem->has($item->id));
            @endphp

            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Purchase Order</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $purchaseOrder->po_number }} — {{ $purchaseOrder->vendor->name }}</div>
                    </div>
                    @if (! $goodsReceipt)
                        <a href="{{ route('goods-receipts.create') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Change purchase order</a>
                    @endif
                </div>

                <form method="POST"
                      action="{{ $goodsReceipt ? route('goods-receipts.update', $goodsReceipt) : route('goods-receipts.store') }}"
                      class="space-y-8">
                    @csrf
                    @if ($goodsReceipt)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="receipt_date" value="Receipt Date *" />
                            <x-text-input id="receipt_date" name="receipt_date" type="date" class="mt-1 block w-full" :value="old('receipt_date', $goodsReceipt?->receipt_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('receipt_date')" class="mt-2" />
                        </div>
                    </div>

                    <x-ui.section title="Items to Receive">
                        @if ($rows->isEmpty())
                            <x-ui.alert variant="success">Everything on this purchase order has already been received.</x-ui.alert>
                        @else
                            <div class="overflow-x-auto -mx-6">
                                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                                            <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide w-24">Ordered</th>
                                            <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide w-24">Remaining</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide w-28">Receiving Now</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                        @foreach ($rows as $index => $item)
                                            @php
                                                $existing = $existingByPoItem->get($item->id);
                                                $remaining = $remainingByItem[$item->id] ?? 0;
                                                $defaultQty = old("items.$index.quantity_received", $existing?->quantity_received ?? $remaining);
                                            @endphp
                                            <tr>
                                                <td class="px-6 py-2 text-sm text-ink-900 dark:text-ink-50">
                                                    {{ $item->product->name }} ({{ $item->product->sku }})
                                                    <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                                </td>
                                                <td class="px-3 py-2 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->quantity, 2) }}</td>
                                                <td class="px-3 py-2 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($remaining, 2) }}</td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0.01" max="{{ $remaining }}" name="items[{{ $index }}][quantity_received]" value="{{ $defaultQty }}" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="text" name="items[{{ $index }}][notes]" value="{{ old("items.$index.notes", $existing?->notes) }}" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <x-input-error :messages="$errors->get('items')" class="mt-2" />
                        @endif
                    </x-ui.section>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $goodsReceipt?->notes) }}</textarea>
                    </div>

                    <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                        @if ($rows->isNotEmpty())
                            <x-primary-button>{{ $goodsReceipt ? __('Update Draft') : __('Save as Draft') }}</x-primary-button>
                        @endif
                        <a href="{{ $goodsReceipt ? route('goods-receipts.show', $goodsReceipt) : route('goods-receipts.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                    </div>
                </form>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

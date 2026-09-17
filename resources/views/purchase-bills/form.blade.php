<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$bill ? 'Edit Purchase Bill '.$bill->bill_number : 'New Purchase Bill'" />
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @if (! $goodsReceipt)
            <x-ui.card>
                <form method="GET" action="{{ route('purchase-bills.create') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[280px]">
                        <x-input-label for="goods_receipt_id" value="Select a completed Goods Receipt to bill *" />
                        <select id="goods_receipt_id" name="goods_receipt_id" onchange="this.form.submit()" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select a goods receipt</option>
                            @foreach ($eligibleGoodsReceipts as $grn)
                                <option value="{{ $grn->id }}">{{ $grn->grn_number }} — {{ $grn->purchaseOrder->vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                @if ($eligibleGoodsReceipts->isEmpty())
                    <x-ui.empty-state title="No unbilled goods receipts" description="Complete a goods receipt against a purchase order before creating a bill." />
                @endif
            </x-ui.card>
        @else
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Goods Receipt</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $goodsReceipt->grn_number }} — {{ $goodsReceipt->purchaseOrder->vendor->name }}</div>
                    </div>
                    @if (! $bill)
                        <a href="{{ route('purchase-bills.create') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Change goods receipt</a>
                    @endif
                </div>

                <form method="POST" action="{{ $bill ? route('purchase-bills.update', $bill) : route('purchase-bills.store') }}" class="space-y-8">
                    @csrf
                    @if ($bill)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="goods_receipt_id" value="{{ $goodsReceipt->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="bill_date" value="Bill Date *" />
                            <x-text-input id="bill_date" name="bill_date" type="date" class="mt-1 block w-full" :value="old('bill_date', $bill?->bill_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('bill_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="due_date" value="Due Date" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', $bill?->due_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tds_section_id" value="TDS Section" />
                            <select id="tds_section_id" name="tds_section_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">None</option>
                                @foreach ($tdsSections as $tdsSection)
                                    <option value="{{ $tdsSection->id }}" @selected(old('tds_section_id', $bill?->tds_section_id) == $tdsSection->id)>{{ $tdsSection->section }} ({{ $tdsSection->rate }}%)</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tds_section_id')" class="mt-2" />
                        </div>
                    </div>

                    <x-ui.section title="Items (from Goods Receipt — quantity and price are locked)">
                        <div class="overflow-x-auto -mx-6">
                            <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Unit Price</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Taxable Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                    @php $taxableTotal = 0; @endphp
                                    @foreach ($goodsReceipt->items as $item)
                                        @php
                                            $qty = (float) $item->quantity_received;
                                            $price = (float) $item->purchaseOrderItem->unit_price;
                                            $lineTotal = round($qty * $price, 2);
                                            $taxableTotal += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-2 text-sm text-ink-900 dark:text-ink-50">{{ $item->purchaseOrderItem->product->name }} ({{ $item->purchaseOrderItem->product->sku }})</td>
                                            <td class="px-3 py-2 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($qty, 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($price, 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($lineTotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-ink-200 dark:border-ink-700">
                                        <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Taxable Total</td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50">{{ number_format($taxableTotal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <p class="text-xs text-ink-400 dark:text-ink-500 mt-2">GST is calculated automatically from each product's GST rate and saved with the bill.</p>
                    </x-ui.section>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $bill?->notes) }}</textarea>
                    </div>

                    <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                        <x-primary-button>{{ $bill ? __('Update Bill') : __('Save as Draft') }}</x-primary-button>
                        <a href="{{ $bill ? route('purchase-bills.show', $bill) : route('purchase-bills.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                    </div>
                </form>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>

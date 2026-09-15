<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Compare Quotations">
            <x-slot name="actions">
                <a href="{{ route('vendor-quotations.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Quotations</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4"
         x-data="{
             quotations: @js($quotations->map(fn ($quotation) => [
                 'id' => $quotation->id,
                 'number' => $quotation->quotation_number,
                 'vendor' => $quotation->vendor->name,
                 'status' => $quotation->status,
                 'items' => $quotation->items->map(fn ($item) => [
                     'product_id' => $item->product_id,
                     'product' => $item->product->name,
                     'quantity' => (float) $item->quantity,
                     'unit_price' => (float) $item->unit_price,
                     'amount' => (float) $item->amount,
                 ]),
                 'total' => (float) $quotation->items->sum('amount'),
             ])),
             get products() {
                 const seen = new Map();
                 this.quotations.forEach(q => q.items.forEach(i => seen.set(i.product_id, i.product)));
                 return Array.from(seen, ([id, name]) => ({ id, name }));
             },
             priceFor(quotation, productId) {
                 const item = quotation.items.find(i => i.product_id === productId);
                 return item ? item.unit_price.toFixed(2) : '—';
             },
             isLowest(productId, quotation) {
                 const prices = this.quotations
                     .map(q => q.items.find(i => i.product_id === productId))
                     .filter(Boolean)
                     .map(i => i.unit_price);
                 const item = quotation.items.find(i => i.product_id === productId);
                 return item && prices.length > 1 && item.unit_price === Math.min(...prices);
             }
         }">
        <x-ui.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Select quotations to compare (Submitted or Approved)</label>
                    <select name="ids[]" multiple size="5" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($allQuotations as $option)
                            <option value="{{ $option->id }}" @selected(in_array($option->id, request('ids', [])))>{{ $option->quotation_number }} — {{ $option->vendor->name }} ({{ $option->status }})</option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="submit">Compare</x-secondary-button>
            </form>
        </x-ui.card>

        <template x-if="quotations.length === 0">
            <x-ui.card>
                <x-ui.empty-state title="No quotations selected" description="Pick two or more quotations above to compare their pricing side by side." />
            </x-ui.card>
        </template>

        <template x-if="quotations.length > 0">
            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                                <template x-for="quotation in quotations" :key="quotation.id">
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">
                                        <span x-text="quotation.number"></span>
                                        <div class="text-[10px] font-normal normal-case text-ink-400" x-text="quotation.vendor"></div>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            <template x-for="product in products" :key="product.id">
                                <tr>
                                    <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50" x-text="product.name"></td>
                                    <template x-for="quotation in quotations" :key="quotation.id + '-' + product.id">
                                        <td class="px-4 py-3.5 text-sm text-right"
                                            :class="isLowest(product.id, quotation) ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-ink-600 dark:text-ink-300'"
                                            x-text="priceFor(quotation, product.id)"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-ink-200 dark:border-ink-700">
                                <td class="px-4 py-3 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Total</td>
                                <template x-for="quotation in quotations" :key="'total-' + quotation.id">
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50" x-text="quotation.total.toFixed(2)"></td>
                                </template>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>
        </template>
    </div>
</x-app-layout>

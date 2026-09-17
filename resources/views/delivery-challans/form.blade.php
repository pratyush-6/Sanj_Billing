<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$challan ? 'Edit Delivery Challan '.$challan->challan_number : 'New Delivery Challan'" />
    </x-slot>

    <div class="max-w-5xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($saleOrder)
            <x-ui.alert variant="info">Converting from sale order {{ $saleOrder->order_number }} ({{ $saleOrder->party->name }}). Review the pre-filled items before saving.</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST"
                  action="{{ $challan ? route('delivery-challans.update', $challan) : route('delivery-challans.store') }}"
                  class="space-y-8"
                  x-data="{
                      products: @js($products->map(fn ($product) => ['id' => $product->id, 'label' => $product->name.' ('.$product->sku.')'])),
                      items: @js($challan
                          ? $challan->items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => (float) $item->quantity, 'notes' => $item->notes])
                          : ($saleOrder
                              ? $saleOrder->items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => (float) $item->quantity, 'notes' => $item->notes])
                              : [['product_id' => '', 'quantity' => null, 'notes' => '']])),
                      addItem() {
                          this.items.push({ product_id: '', quantity: null, notes: '' });
                      },
                      removeItem(index) {
                          if (this.items.length > 1) {
                              this.items.splice(index, 1);
                          }
                      }
                  }">
                @csrf
                @if ($challan)
                    @method('PUT')
                @endif
                @if ($saleOrder)
                    <input type="hidden" name="sale_order_id" value="{{ $saleOrder->id }}">
                @endif

                <x-ui.section title="Challan Details">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="party_id" value="Customer *" />
                            <select id="party_id" name="party_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                <option value="">Select</option>
                                @foreach ($parties as $party)
                                    <option value="{{ $party->id }}" @selected((string) old('party_id', $challan?->party_id ?? $saleOrder?->party_id) === (string) $party->id)>{{ $party->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('party_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="challan_date" value="Challan Date *" />
                            <x-text-input id="challan_date" name="challan_date" type="date" class="mt-1 block w-full" :value="old('challan_date', $challan?->challan_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('challan_date')" class="mt-2" />
                        </div>
                    </div>
                </x-ui.section>

                <x-ui.section title="Line Items">
                    <div class="overflow-x-auto -mx-6">
                        <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                            <thead>
                                <tr>
                                    <th class="px-6 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide w-32">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Notes</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-6 py-2">
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                                <option value="">Select product</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="product.id" x-text="product.label" :selected="item.product_id == product.id"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="`items[${index}][notes]`" x-model="item.notes" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-ink-400 hover:text-rose-600 dark:hover:text-rose-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="px-6 py-3">
                                        <button type="button" @click="addItem()" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300">+ Add Line</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="mt-2" />
                </x-ui.section>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $challan?->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ $challan ? __('Update Challan') : __('Save as Draft') }}</x-primary-button>
                    <a href="{{ $challan ? route('delivery-challans.show', $challan) : route('delivery-challans.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

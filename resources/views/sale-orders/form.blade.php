<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$order ? 'Edit Sale Order '.$order->order_number : 'New Sale Order'" />
    </x-slot>

    <div class="max-w-5xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST"
                  action="{{ $order ? route('sale-orders.update', $order) : route('sale-orders.store') }}"
                  class="space-y-8"
                  x-data="{
                      products: @js($products->map(fn ($product) => ['id' => $product->id, 'label' => $product->displayLabel(), 'unit_code' => $product->unit?->name, 'secondary_unit_code' => $product->secondaryUnit?->name, 'conversion_factor' => $product->conversion_factor ? (float) $product->conversion_factor : null, 'stock' => (float) ($stockLevels[$product->id] ?? 0)])),
                      items: @js($order
                          ? $order->items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => (float) $item->quantity, 'unit_price' => (float) $item->unit_price, 'unit_type' => 'primary', 'notes' => $item->notes])
                          : [['product_id' => '', 'quantity' => null, 'unit_price' => null, 'unit_type' => 'primary', 'notes' => '']]),
                      addItem() {
                          this.items.push({ product_id: '', quantity: null, unit_price: null, unit_type: 'primary', notes: '' });
                      },
                      removeItem(index) {
                          if (this.items.length > 1) {
                              this.items.splice(index, 1);
                          }
                      },
                      product(item) {
                          return this.products.find(p => p.id == item.product_id) ?? null;
                      },
                      hasSecondaryUnit(item) {
                          const product = this.product(item);
                          return !!(product && product.secondary_unit_code && product.conversion_factor);
                      },
                      unitCode(item) {
                          const product = this.product(item);
                          if (!product) return null;
                          return item.unit_type === 'secondary' ? product.secondary_unit_code : product.unit_code;
                      },
                      conversionHint(item) {
                          const product = this.product(item);
                          if (!product || !product.secondary_unit_code || !product.conversion_factor) return null;
                          const qty = parseFloat(item.quantity) || 0;
                          return item.unit_type === 'secondary'
                              ? '= ' + (qty / product.conversion_factor).toFixed(2) + ' ' + product.unit_code
                              : '= ' + (qty * product.conversion_factor).toFixed(2) + ' ' + product.secondary_unit_code;
                      },
                      stockInSelectedUnit(item) {
                          const product = this.product(item);
                          if (!product) return null;
                          return item.unit_type === 'secondary' ? product.stock * product.conversion_factor : product.stock;
                      },
                      stockLabel(item) {
                          const product = this.product(item);
                          if (!product) return null;
                          return '{{ ui_label('In stock') }}: ' + this.stockInSelectedUnit(item).toFixed(2) + ' ' + (this.unitCode(item) ?? '');
                      },
                      lowStock(item) {
                          return (parseFloat(item.quantity) || 0) > (this.stockInSelectedUnit(item) ?? Infinity);
                      },
                      get total() {
                          return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0).toFixed(2);
                      }
                  }">
                @csrf
                @if ($order)
                    @method('PUT')
                @endif

                <x-ui.section title="Order Details">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="party_id" :value="ui_label('Customer *')" />
                            <select id="party_id" name="party_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                <option value="">Select</option>
                                @foreach ($parties as $party)
                                    <option value="{{ $party->id }}" @selected((string) old('party_id', $order?->party_id) === (string) $party->id)>{{ $party->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('party_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="order_date" :value="ui_label('Order Date *')" />
                            <x-text-input id="order_date" name="order_date" type="date" class="mt-1 block w-full" :value="old('order_date', $order?->order_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('order_date')" class="mt-2" />
                        </div>
                    </div>
                </x-ui.section>

                <x-ui.section title="Line Items">
                    <div class="overflow-x-auto -mx-6">
                        <table class="w-full min-w-[64rem] divide-y divide-ink-100 dark:divide-ink-800">
                            <thead>
                                <tr>
                                    <th class="px-6 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[16rem]">{{ __('common.product') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[8rem]">{{ ui_label('Unit') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[9rem]">{{ __('common.qty') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[8rem]">{{ __('common.unit_price') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[8rem]">{{ __('common.amount') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[10rem]">{{ __('common.notes') }}</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-6 py-2">
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="item.unit_type = 'primary'" class="block w-full min-w-[14rem] bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                                <option value="">Select product</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="product.id" x-text="product.label" :selected="item.product_id == product.id"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <template x-if="hasSecondaryUnit(item)">
                                                <select :name="`items[${index}][unit_type]`" x-model="item.unit_type" class="block w-full min-w-[7rem] bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                                    <option value="primary" x-text="product(item).unit_code"></option>
                                                    <option value="secondary" x-text="product(item).secondary_unit_code"></option>
                                                </select>
                                            </template>
                                            <template x-if="!hasSecondaryUnit(item)">
                                                <div>
                                                    <input type="hidden" :name="`items[${index}][unit_type]`" value="primary">
                                                    <span class="text-sm text-ink-500 dark:text-ink-400" x-text="unitCode(item) ?? '—'"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-1.5">
                                                <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" class="block w-28 min-w-[6rem] text-right bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                            </div>
                                            <p class="text-xs mt-1" :class="lowStock(item) ? 'text-rose-500 dark:text-rose-400 font-medium' : 'text-ink-400 dark:text-ink-500'" x-show="item.product_id" x-text="stockLabel(item)"></p>
                                            <p class="text-xs text-ink-400 dark:text-ink-500 mt-1" x-show="conversionHint(item)" x-text="conversionHint(item)"></p>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" class="block w-full min-w-[6rem] text-right bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                        </td>
                                        <td class="px-3 py-2 text-right text-sm text-ink-700 dark:text-ink-200" x-text="((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(2)"></td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="`items[${index}][notes]`" x-model="item.notes" class="block w-full min-w-[9rem] bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
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
                                    <td class="px-3 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50" x-text="total"></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="mt-2" />
                </x-ui.section>

                <div>
                    <x-input-label for="notes" :value="ui_label('Notes')" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $order?->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ $order ? __('Update Order') : __('Save as Draft') }}</x-primary-button>
                    <a href="{{ $order ? route('sale-orders.show', $order) : route('sale-orders.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

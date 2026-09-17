<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$invoice ? 'Edit Sale Invoice '.$invoice->invoice_number : 'New Sale Invoice'" />
    </x-slot>

    <div class="max-w-5xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($challan)
            <x-ui.alert variant="info">Invoicing delivery challan {{ $challan->challan_number }} ({{ $challan->party->name }}). Only the undelivered-but-unbilled balance of each line is shown.</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST"
                  action="{{ $invoice ? route('sale-invoices.update', $invoice) : route('sale-invoices.store') }}"
                  class="space-y-8"
                  @if (! $invoice)
                  x-data="{
                      products: @js($products->map(fn ($product) => ['id' => $product->id, 'label' => $product->name.' ('.$product->sku.')'])),
                      lockedItems: {{ $challan ? 'true' : 'false' }},
                      items: @js($challan
                          ? $challanLines->map(fn ($line) => ['delivery_challan_item_id' => $line['delivery_challan_item_id'], 'product_id' => $line['product_id'], 'product_label' => $line['product_label'], 'quantity' => $line['remaining'], 'max' => $line['remaining'], 'unit_price' => null])
                          : [['product_id' => '', 'quantity' => null, 'unit_price' => null]]),
                      addItem() {
                          this.items.push({ product_id: '', quantity: null, unit_price: null });
                      },
                      removeItem(index) {
                          if (this.items.length > 1) {
                              this.items.splice(index, 1);
                          }
                      },
                      get total() {
                          return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0).toFixed(2);
                      }
                  }"
                  @endif>
                @csrf
                @if ($invoice)
                    @method('PUT')
                @endif
                @if ($challan)
                    <input type="hidden" name="party_id" value="{{ $challan->party_id }}">
                @endif

                <x-ui.section title="Invoice Details">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label value="Customer *" />
                            @if ($invoice)
                                <div class="mt-1 text-sm font-medium text-ink-900 dark:text-ink-50 py-2">{{ $invoice->party->name }}</div>
                            @elseif ($challan)
                                <div class="mt-1 text-sm font-medium text-ink-900 dark:text-ink-50 py-2">{{ $challan->party->name }}</div>
                            @else
                                <select id="party_id" name="party_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                    <option value="">Select</option>
                                    @foreach ($parties as $party)
                                        <option value="{{ $party->id }}" @selected((string) old('party_id') === (string) $party->id)>{{ $party->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('party_id')" class="mt-2" />
                            @endif
                        </div>

                        <div>
                            <x-input-label for="invoice_date" value="Invoice Date *" />
                            <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" :value="old('invoice_date', $invoice?->invoice_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('invoice_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="due_date" value="Due Date" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', $invoice?->due_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tds_section_id" value="TDS Section" />
                            <select id="tds_section_id" name="tds_section_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">None</option>
                                @foreach ($tdsSections as $tdsSection)
                                    <option value="{{ $tdsSection->id }}" @selected(old('tds_section_id', $invoice?->tds_section_id) == $tdsSection->id)>{{ $tdsSection->section }} ({{ $tdsSection->rate }}%)</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tds_section_id')" class="mt-2" />
                        </div>
                    </div>
                </x-ui.section>

                <x-ui.section title="Line Items">
                    @if ($invoice)
                        <div class="overflow-x-auto -mx-6">
                            <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Unit Price</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                    @foreach ($invoice->items as $item)
                                        <tr>
                                            <td class="px-6 py-2 text-sm text-ink-900 dark:text-ink-50">{{ $item->product->name }} ({{ $item->product->sku }})</td>
                                            <td class="px-3 py-2 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->quantity, 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-ink-400 dark:text-ink-500 mt-2">Line items are locked once an invoice is created. Cancel and create a new invoice to change quantities or pricing.</p>
                    @else
                        <div class="overflow-x-auto -mx-6">
                            <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide w-28">Qty</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide w-32">Unit Price</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide w-28">Amount</th>
                                        @unless ($challan)
                                            <th class="px-3 py-2 w-10"></th>
                                        @endunless
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-6 py-2">
                                                <template x-if="lockedItems">
                                                    <div>
                                                        <span x-text="item.product_label" class="text-sm text-ink-900 dark:text-ink-50"></span>
                                                        <input type="hidden" :name="`items[${index}][delivery_challan_item_id]`" :value="item.delivery_challan_item_id">
                                                        <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                                    </div>
                                                </template>
                                                <template x-if="!lockedItems">
                                                    <select :name="`items[${index}][product_id]`" x-model="item.product_id" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                                        <option value="">Select product</option>
                                                        <template x-for="product in products" :key="product.id">
                                                            <option :value="product.id" x-text="product.label" :selected="item.product_id == product.id"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" step="0.01" min="0.01" :max="item.max ?? null" :name="`items[${index}][quantity]`" x-model.number="item.quantity" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                                <p class="text-xs text-ink-400 dark:text-ink-500 mt-1" x-show="item.max" x-text="'Max: ' + item.max"></p>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" class="block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                            </td>
                                            <td class="px-3 py-2 text-right text-sm text-ink-700 dark:text-ink-200" x-text="((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(2)"></td>
                                            <template x-if="!lockedItems">
                                                <td class="px-3 py-2 text-right">
                                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-ink-400 hover:text-rose-600 dark:hover:text-rose-400">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="px-6 py-3">
                                            <template x-if="!lockedItems">
                                                <button type="button" @click="addItem()" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300">+ Add Line</button>
                                            </template>
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50" colspan="2" x-text="'Total: ' + total"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <x-input-error :messages="$errors->get('items')" class="mt-2" />
                        <p class="text-xs text-ink-400 dark:text-ink-500 mt-2">GST is calculated automatically from each product's GST rate and saved with the invoice.</p>
                    @endif
                </x-ui.section>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $invoice?->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ $invoice ? __('Update Invoice') : __('Save as Draft') }}</x-primary-button>
                    <a href="{{ $invoice ? route('sale-invoices.show', $invoice) : route('sale-invoices.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Stock Transfer">
            <x-slot name="actions">
                <a href="{{ route('stock-transfers.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Transfers</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($destinations->isEmpty())
            <x-ui.alert variant="warning">Create at least one more active branch before transferring stock.</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('stock-transfers.store') }}" class="space-y-6"
                  x-data="{
                      items: @js(old('items', [['product_id' => '', 'quantity' => null]])),
                      addItem() { this.items.push({ product_id: '', quantity: null }); },
                      removeItem(index) { this.items.splice(index, 1); },
                  }">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <x-input-label value="From branch" />
                        <div class="mt-1 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $from?->name }}</div>
                        <p class="text-xs text-ink-400 dark:text-ink-500 mt-1">The branch selected at the top of the page.</p>
                    </div>

                    <div>
                        <x-input-label for="to_branch_id" value="To branch *" />
                        <select id="to_branch_id" name="to_branch_id" required class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select</option>
                            @foreach ($destinations as $destination)
                                <option value="{{ $destination->id }}" @selected(old('to_branch_id') == $destination->id)>{{ $destination->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('to_branch_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="transfer_date" value="Transfer date *" />
                        <x-text-input id="transfer_date" name="transfer_date" type="date" class="mt-1 block w-full" :value="old('transfer_date', date('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('transfer_date')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label value="Products" />
                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full min-w-[32rem] divide-y divide-ink-100 dark:divide-ink-800">
                            <thead>
                                <tr>
                                    <th class="px-2 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[16rem]">Product</th>
                                    <th class="px-2 py-2 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide min-w-[9rem]">Quantity</th>
                                    <th class="px-2 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-2 py-2">
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" required class="block w-full min-w-[14rem] bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                                <option value="">Select product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->displayLabel() }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" required class="block w-28 min-w-[6rem] text-right bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                        </td>
                                        <td class="px-2 py-2 text-right">
                                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-ink-400 hover:text-rose-600 dark:hover:text-rose-400 text-sm">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" @click="addItem()" class="mt-2 text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300">+ Add product</button>
                    <x-input-error :messages="$errors->get('items')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button :disabled="$destinations->isEmpty()">Complete Transfer</x-primary-button>
                    <a href="{{ route('stock-transfers.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

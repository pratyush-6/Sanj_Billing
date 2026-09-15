<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$vendor->name">
            <x-slot name="actions">
                <a href="{{ route('vendors.edit', $vendor) }}">
                    <x-secondary-button>Edit Vendor</x-secondary-button>
                </a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-ink-400 dark:text-ink-500 text-xs uppercase tracking-wide">Contact</p><p class="font-medium text-ink-900 dark:text-ink-50 mt-0.5">{{ $vendor->contact_person ?? '—' }}</p></div>
                <div><p class="text-ink-400 dark:text-ink-500 text-xs uppercase tracking-wide">Mobile</p><p class="font-medium text-ink-900 dark:text-ink-50 mt-0.5">{{ $vendor->mobile ?? '—' }}</p></div>
                <div><p class="text-ink-400 dark:text-ink-500 text-xs uppercase tracking-wide">GSTIN</p><p class="font-medium text-ink-900 dark:text-ink-50 mt-0.5">{{ $vendor->gstin ?? '—' }}</p></div>
                <div><p class="text-ink-400 dark:text-ink-500 text-xs uppercase tracking-wide">PAN</p><p class="font-medium text-ink-900 dark:text-ink-50 mt-0.5">{{ $vendor->pan ?? '—' }}</p></div>
                <div><p class="text-ink-400 dark:text-ink-500 text-xs uppercase tracking-wide">Total Spent</p><p class="font-medium text-ink-900 dark:text-ink-50 mt-0.5">₹{{ number_format($totalSpent, 2) }}</p></div>
            </div>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-700 dark:text-ink-200">Products &amp; Pricing</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Vendor SKU</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Lead Time (days)</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Preferred</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($vendorProducts as $vendorProduct)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $vendorProduct->product->name }} <span class="text-ink-400 dark:text-ink-500 font-normal">({{ $vendorProduct->product->sku }})</span></td>
                                <td class="px-4 py-3.5 text-sm">
                                    <input form="vp-form-{{ $vendorProduct->id }}" type="text" name="vendor_sku" value="{{ $vendorProduct->vendor_sku }}" class="w-28 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                                </td>
                                <td class="px-4 py-3.5 text-sm text-right">
                                    <input form="vp-form-{{ $vendorProduct->id }}" type="number" step="0.01" min="0" name="price" value="{{ $vendorProduct->price }}" class="w-24 text-right bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-right">
                                    <input form="vp-form-{{ $vendorProduct->id }}" type="number" min="0" name="lead_time_days" value="{{ $vendorProduct->lead_time_days }}" class="w-16 text-right bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                                </td>
                                <td class="px-4 py-3.5 text-sm">
                                    <input form="vp-form-{{ $vendorProduct->id }}" type="checkbox" name="is_preferred" value="1" @checked($vendorProduct->is_preferred) class="rounded bg-white dark:bg-ink-800 border-ink-300 dark:border-ink-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm whitespace-nowrap">
                                    <button form="vp-form-{{ $vendorProduct->id }}" type="submit" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">Save</button>
                                    <form method="POST" action="{{ route('vendors.products.destroy', [$vendor, $vendorProduct]) }}" class="inline" onsubmit="return confirm('Remove this product pricing?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300 font-medium ml-2">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state title="No product pricing recorded yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>

                @foreach ($vendorProducts as $vendorProduct)
                    <form method="POST" action="{{ route('vendors.products.update', [$vendor, $vendorProduct]) }}" id="vp-form-{{ $vendorProduct->id }}">
                        @csrf
                        @method('PUT')
                    </form>
                @endforeach

                @if ($availableProducts->isNotEmpty())
                    <form method="POST" action="{{ route('vendors.products.store', $vendor) }}" class="flex flex-wrap items-end gap-3 px-4 py-4 border-t border-ink-100 dark:border-ink-800">
                        @csrf
                        <div>
                            <x-input-label value="Add Product" class="!mb-1" />
                            <select name="product_id" class="bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                <option value="">Select product</option>
                                @foreach ($availableProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Price" class="!mb-1" />
                            <input type="number" step="0.01" min="0" name="price" class="w-28 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
                        </div>
                        <div>
                            <x-input-label value="Vendor SKU" class="!mb-1" />
                            <input type="text" name="vendor_sku" class="w-32 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <x-input-label value="Lead Time (days)" class="!mb-1" />
                            <input type="number" min="0" name="lead_time_days" class="w-24 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <x-secondary-button type="submit">Add</x-secondary-button>
                    </form>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-700 dark:text-ink-200">Expense History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Expense #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($expenses as $expense)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $expense->expense_number }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->expense_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->category->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($expense->total_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="match($expense->status) { 'Approved' => 'success', 'Draft' => 'warning', 'Cancelled' => 'danger', default => 'neutral' }">{{ $expense->status }}</x-ui.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No expenses recorded for this vendor yet" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $expenses->links() }}</div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Stock Adjustment" />
    </x-slot>

    <div class="max-w-2xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('stock-adjustments.store') }}" class="space-y-6" x-data="{ type: '{{ old('type', $types[0] ?? 'Increase') }}' }">
                @csrf

                <div>
                    <x-input-label for="product_id" value="Product *" />
                    <select id="product_id" name="product_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                        <option value="">Select</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="adjustment_date" value="Adjustment Date *" />
                        <x-text-input id="adjustment_date" name="adjustment_date" type="date" class="mt-1 block w-full" :value="old('adjustment_date', date('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('adjustment_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" value="Type *" />
                        <select id="type" name="type" x-model="type" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                            @foreach ($types as $type)
                                <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="quantity" value="Quantity *" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('quantity')" required />
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>

                    <div x-show="type === 'Increase'">
                        <x-input-label for="unit_cost" value="Unit Cost" />
                        <x-text-input id="unit_cost" name="unit_cost" type="number" step="0.0001" min="0" class="mt-1 block w-full" placeholder="Leave blank to use current average cost" :value="old('unit_cost')" />
                        <x-input-error :messages="$errors->get('unit_cost')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reason" value="Reason *" />
                        <select id="reason" name="reason" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                            <option value="">Select</option>
                            @foreach ($reasons as $reason)
                                <option value="{{ $reason }}" @selected(old('reason') === $reason)>{{ $reason }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>Submit for Approval</x-primary-button>
                    <a href="{{ route('stock-adjustments.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

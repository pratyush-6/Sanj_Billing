<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="sku" value="SKU *" />
        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $product?->sku)" required />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name" value="Product Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="product_category_id" value="Category" />
        <select id="product_category_id" name="product_category_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">None</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('product_category_id', $product?->product_category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('product_category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="unit_id" value="Unit" />
        <select id="unit_id" name="unit_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">None</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $product?->unit_id) == $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hsn_code" value="HSN/SAC Code" />
        <x-text-input id="hsn_code" name="hsn_code" type="text" class="mt-1 block w-full" :value="old('hsn_code', $product?->hsn_code)" />
        <x-input-error :messages="$errors->get('hsn_code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="gst_rate_id" value="GST Rate" />
        <select id="gst_rate_id" name="gst_rate_id" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">None</option>
            @foreach ($gstRates as $gstRate)
                <option value="{{ $gstRate->id }}" @selected(old('gst_rate_id', $product?->gst_rate_id) == $gstRate->id)>{{ $gstRate->name }} ({{ $gstRate->rate }}%)</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('gst_rate_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="min_stock_level" value="Minimum Stock Level" />
        <x-text-input id="min_stock_level" name="min_stock_level" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('min_stock_level', $product?->min_stock_level ?? 0)" />
        <x-input-error :messages="$errors->get('min_stock_level')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="max_stock_level" value="Maximum Stock Level" />
        <x-text-input id="max_stock_level" name="max_stock_level" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('max_stock_level', $product?->max_stock_level)" />
        <x-input-error :messages="$errors->get('max_stock_level')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            <option value="active" @selected(old('status', $product?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $product?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="2" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description', $product?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

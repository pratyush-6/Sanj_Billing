<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" value="Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="e.g. GST 18%" :value="old('name', $gstRate?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="rate" value="Rate (%) *" />
        <x-text-input id="rate" name="rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('rate', $gstRate?->rate)" required />
        <x-input-error :messages="$errors->get('rate')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            <option value="active" @selected(old('status', $gstRate?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $gstRate?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
</div>

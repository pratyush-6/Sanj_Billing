<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" value="Category Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="code" value="Category Code" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $category?->code)" />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="expense_nature" value="Expense Nature" />
        <select id="expense_nature" name="expense_nature" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Select</option>
            @foreach ($natureOptions as $option)
                <option value="{{ $option }}" @selected(old('expense_nature', $category?->expense_nature) === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('expense_nature')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            <option value="active" @selected(old('status', $category?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $category?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $category?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="tax_applicable" value="0">
        <input type="checkbox" id="tax_applicable" name="tax_applicable" value="1" class="rounded border-gray-300" @checked(old('tax_applicable', $category?->tax_applicable))>
        <x-input-label for="tax_applicable" value="Tax applicable by default" class="!mb-0" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="expense_category_id" value="Category *" />
        <select id="expense_category_id" name="expense_category_id" class="mt-1 block w-full border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            <option value="">Select category</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) old('expense_category_id', $subCategory?->expense_category_id) === (string) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name" value="Sub Category Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $subCategory?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $subCategory?->code)" />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            <option value="active" @selected(old('status', $subCategory?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $subCategory?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description', $subCategory?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" value="Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $unit?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $unit?->code)" />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            <option value="active" @selected(old('status', $unit?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $unit?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
</div>

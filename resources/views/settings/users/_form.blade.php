<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" value="Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email *" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" value="Role *" />
        <select id="role" name="role" class="mt-1 block w-full border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            <option value="">Select role</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $user?->roles->first()?->name) === $role)>{{ $role }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            <option value="active" @selected(old('status', $user?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $user?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$user ? 'New Password (leave blank to keep current)' : 'Password *'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $user" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirm Password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
    </div>
</div>

<div class="mt-6 pt-6 border-t border-ink-100">
    <x-input-label value="Company Access" />
    <p class="text-xs text-ink-400 mb-3">Which companies can this user see and work in. Super Admins automatically have access to every company.</p>
    <div class="space-y-2">
        @forelse ($companies as $company)
            @php($selected = old('companies', $user?->companies->pluck('id')->all() ?? []))
            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input type="checkbox" name="companies[]" value="{{ $company->id }}" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500"
                    @checked(in_array($company->id, $selected))>
                {{ $company->name }}
            </label>
        @empty
            <p class="text-sm text-ink-400">No companies exist yet.</p>
        @endforelse
    </div>
    <x-input-error :messages="$errors->get('companies')" class="mt-2" />
</div>

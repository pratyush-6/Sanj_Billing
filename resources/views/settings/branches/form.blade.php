<x-app-layout>
    @php($isEdit = $branch !== null)

    <x-slot name="header">
        <x-ui.page-header :title="$isEdit ? 'Edit Branch' : 'Add Branch'">
            <x-slot name="actions">
                <a href="{{ route('branches.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Branches</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-2xl space-y-4">
        <x-ui.card>
            <form method="POST" action="{{ $isEdit ? route('branches.update', $branch) : route('branches.store') }}" class="space-y-6">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="name" value="Branch Name *" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $branch?->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="code" value="Code *" />
                        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full uppercase" :value="old('code', $branch?->code)" required />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="address" value="Address" />
                        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $branch?->address)" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="state" value="State" />
                        <select id="state" name="state" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Select</option>
                            @foreach (config('india.states') as $state)
                                <option value="{{ $state }}" @selected(old('state', $branch?->state) === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('state')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status *" />
                        <select id="status" name="status" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                            <option value="active" @selected(old('status', $branch?->status ?? 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $branch?->status) === 'inactive')>Inactive</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label value="Users who can work in this branch" />
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($companyUsers as $user)
                            <label class="flex items-center gap-2 text-sm text-ink-700 dark:text-ink-200">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                    class="rounded bg-white dark:bg-ink-800 border-ink-300 dark:border-ink-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500"
                                    @checked(in_array($user->id, old('user_ids', $assignedUserIds)))>
                                {{ $user->name }}
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-ink-400 dark:text-ink-500 mt-2">Super Admins can access every branch of the company without being listed here.</p>
                </div>

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ $isEdit ? 'Save Branch' : 'Create Branch' }}</x-primary-button>
                    <a href="{{ route('branches.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

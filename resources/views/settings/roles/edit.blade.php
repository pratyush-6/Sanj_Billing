<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$role->name.' — Permissions'">
            <x-slot name="actions">
                <a href="{{ route('settings.roles.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Roles</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($role->name === 'Super Admin')
            <x-ui.alert variant="info">Super Admin always has every permission in the system — this is fixed and cannot be changed here.</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('settings.roles.update', $role) }}" class="space-y-8">
                @csrf
                @method('PUT')

                @foreach ($grouped as $group => $permissions)
                    <div class="border-b border-ink-100 dark:border-ink-800 pb-6 last:border-0 last:pb-0">
                        <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100 mb-3 capitalize">{{ str_replace('-', ' ', $group) }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm text-ink-700 dark:text-ink-200">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                        class="rounded bg-white dark:bg-ink-800 border-ink-300 dark:border-ink-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500"
                                        @checked(in_array($permission->name, $assigned))
                                        @disabled($role->name === 'Super Admin')>
                                    {{ $permission->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if ($role->name !== 'Super Admin')
                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>Save Permissions</x-primary-button>
                    </div>
                @endif
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

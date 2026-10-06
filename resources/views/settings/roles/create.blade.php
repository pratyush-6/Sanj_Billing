<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Add Role">
            <x-slot name="actions">
                <a href="{{ route('settings.roles.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Roles</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-xl space-y-4">
        <x-ui.card>
            <form method="POST" action="{{ route('settings.roles.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" :value="ui_label('Role Name *')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    <p class="text-xs text-ink-400 dark:text-ink-500 mt-1">You'll set its permissions on the next screen.</p>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Create Role</x-primary-button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

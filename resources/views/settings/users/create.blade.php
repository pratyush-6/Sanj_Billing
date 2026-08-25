<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New User" />
    </x-slot>

    <div class="max-w-2xl">
        <x-ui.card>
            <form method="POST" action="{{ route('settings.users.store') }}" class="space-y-6">
                @csrf
                @include('settings.users._form', ['user' => null, 'roles' => $roles])

                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Create User') }}</x-primary-button>
                    <a href="{{ route('settings.users.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

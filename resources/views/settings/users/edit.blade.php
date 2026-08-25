<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Edit User" />
    </x-slot>

    <div class="max-w-2xl">
        <x-ui.card>
            <form method="POST" action="{{ route('settings.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('settings.users._form', ['user' => $user, 'roles' => $roles])

                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Update User') }}</x-primary-button>
                    <a href="{{ route('settings.users.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

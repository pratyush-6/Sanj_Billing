<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Party" />
    </x-slot>

    <div class="max-w-3xl">
        <x-ui.card>
            <form method="POST" action="{{ route('parties.store') }}" class="space-y-6">
                @csrf
                @include('parties._form', ['party' => null])

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ __('Create Party') }}</x-primary-button>
                    <a href="{{ route('parties.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

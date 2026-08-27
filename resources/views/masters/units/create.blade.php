<x-app-layout>
    <x-slot name="header"><x-ui.page-header title="New Unit" /></x-slot>
    <div class="max-w-xl">
        <x-ui.card>
            <form method="POST" action="{{ route('units.store') }}" class="space-y-6">
                @csrf
                @include('masters.units._form', ['unit' => null])
                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                    <a href="{{ route('units.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

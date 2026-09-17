<x-app-layout>
    <x-slot name="header"><x-ui.page-header title="New GST Rate" /></x-slot>
    <div class="max-w-xl">
        <x-ui.card>
            <form method="POST" action="{{ route('gst-rates.store') }}" class="space-y-6">
                @csrf
                @include('masters.gst-rates._form', ['gstRate' => null])
                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                    <a href="{{ route('gst-rates.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

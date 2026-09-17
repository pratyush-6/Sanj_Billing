<x-app-layout>
    <x-slot name="header"><x-ui.page-header title="Edit TDS Section" /></x-slot>
    <div class="max-w-xl">
        <x-ui.card>
            <form method="POST" action="{{ route('tds-sections.update', $tdsSection) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('masters.tds-sections._form', ['tdsSection' => $tdsSection])
                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>
                    <a href="{{ route('tds-sections.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Financial Year" />
    </x-slot>

    <div class="max-w-xl">
        <x-ui.card>
            <form method="POST" action="{{ route('financial-years.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" value="Name * (e.g. FY 2026-27)" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="start_date" value="Start Date *" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="end_date" value="End Date *" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" required />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                    <a href="{{ route('financial-years.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header"><x-ui.page-header title="Edit Unit" /></x-slot>
    <div class="max-w-xl">
        <x-ui.card>
            <form method="POST" action="{{ route('units.update', $unit) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('masters.units._form', ['unit' => $unit])
                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>
                    <a href="{{ route('units.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>

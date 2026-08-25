<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Unit') }}</h2></x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('units.update', $unit) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('masters.units._form', ['unit' => $unit])
                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                        <a href="{{ route('units.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Profile" description="Manage your account details and password." />
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-ui.card>
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.update-password-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.delete-user-form')
        </x-ui.card>
    </div>
</x-app-layout>

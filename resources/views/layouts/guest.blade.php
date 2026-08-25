<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink-900 antialiased">
        <div class="min-h-screen flex flex-col sm:flex-row">
            <div class="hidden sm:flex sm:w-2/5 bg-brand-700 text-white flex-col justify-between p-10">
                <x-application-logo class="[&_span]:text-white" />
                <div>
                    <p class="text-2xl font-bold leading-snug">Expenses, accounting & tax &mdash; in one place.</p>
                    <p class="mt-3 text-brand-100 text-sm">Record expenses once. Reports, GST/TDS, and CA-ready data follow automatically.</p>
                </div>
                <p class="text-xs text-brand-200">&copy; {{ date('Y') }} Sanjeevani</p>
            </div>

            <div class="flex flex-1 flex-col justify-center items-center px-6 py-12">
                <div class="w-full sm:hidden mb-8">
                    <x-application-logo />
                </div>
                <div class="w-full sm:max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>

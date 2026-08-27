<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <script>
            (function () {
                var theme = localStorage.getItem('theme') || 'system';
                var isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-ink-50 dark:bg-ink-950">
        <div
            x-data="{
                sidebarOpen: false,
                theme: localStorage.getItem('theme') || 'system',
                setTheme(value) {
                    this.theme = value;
                    localStorage.setItem('theme', value);
                    const isDark = value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', isDark);
                },
            }"
            x-init="window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => { if (theme === 'system') setTheme('system'); })"
            class="min-h-screen"
        >

            <!-- Mobile sidebar overlay -->
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-ink-900/50 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

            <!-- Mobile sidebar panel -->
            <div x-show="sidebarOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 w-72 lg:hidden"
                 style="display: none;">
                @include('layouts.sidebar')
            </div>

            <!-- Desktop sidebar -->
            <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col border-e border-ink-200/70 dark:border-ink-800/70">
                @include('layouts.sidebar')
            </div>

            <div class="lg:pl-64">
                <!-- Topbar -->
                <div class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-ink-200/70 dark:border-ink-800/70 bg-white/90 dark:bg-ink-900/90 backdrop-blur px-4 sm:px-6">
                    <button @click="sidebarOpen = true" class="text-ink-500 hover:text-ink-700 dark:text-ink-400 dark:hover:text-ink-200 lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>

                    <div class="flex-1 min-w-0">
                        @isset($header)
                            {{ $header }}
                        @endisset
                    </div>

                    @php($__fy = current_company()?->activeFinancialYear())
                    @if ($__fy)
                        <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-brand-50 dark:bg-brand-500/10 px-3 py-1 text-xs font-medium text-brand-700 dark:text-brand-400 ring-1 ring-inset ring-brand-600/20 dark:ring-brand-400/30 whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                            {{ $__fy->name }}
                        </span>
                    @endif

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = ! open" class="flex items-center text-ink-400 hover:text-ink-600 dark:text-ink-500 dark:hover:text-ink-300 transition" title="Theme">
                            <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                            <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                            <svg x-show="theme === 'system'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute right-0 z-40 mt-2 w-36 rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 shadow-lg py-1 text-sm">
                            @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                                <button type="button" @click="setTheme('{{ $value }}'); open = false"
                                        @class([
                                            'flex w-full items-center justify-between px-3 py-1.5 text-left hover:bg-ink-50 dark:hover:bg-ink-700',
                                        ])
                                        :class="theme === '{{ $value }}' ? 'text-brand-600 dark:text-brand-400 font-medium' : 'text-ink-700 dark:text-ink-200'">
                                    {{ $label }}
                                    <svg x-show="theme === '{{ $value }}'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-ink-400 hover:text-rose-600 dark:text-ink-500 dark:hover:text-rose-400 transition" title="Log out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3H15" />
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Page Content -->
                <main class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lara Dashboard') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>

<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-900">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}">
                        <img class="dark:hidden max-h-[40px]"
                            src="{{ config('settings.site_logo_lite') ?? asset('images/logo/lara-dashboard.png') }}"
                            alt="{{ config('app.name') }}" />
                        <img class="hidden dark:block max-h-[40px]"
                            src="{{ config('settings.site_logo_dark') ?? asset('images/logo/lara-dashboard-dark.png') }}"
                            alt="{{ config('app.name') }}" />
                    </a>
                </div>

                <!-- Right Side -->
                <div class="flex items-center">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                {{ __('Se déconnecter') }}
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex items-center justify-center flex-grow">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
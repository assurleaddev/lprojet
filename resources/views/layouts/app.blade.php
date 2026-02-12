<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" href="{{ config('settings.site_favicon') ?? asset('favicon.ico') }}" type="image/x-icon">

    @include('backend.layouts.partials.theme-colors')
    @yield('before_vite_build')

    @livewireStyles
    @viteReactRefresh
    @vite(['resources/js/app.js', 'resources/css/app.css'], 'build')
    @stack('styles')
    @yield('before_head')
    <style>
        :root {
            --brand: #FC0E00;
            --ink: #1a1a1a;
            --muted: #666;
            --line: #e6e6e6;
            --bg-soft: #f6f6f6;
        }

        /* Base */
        html,
        body {
            height: 100%;
        }

        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            color: var(--ink);
            background: #fff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Container width to match Used feel */
        .shell {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Brand wordmark */
        .brand-text {
            font-weight: 800;
            font-size: 20px;
            color: var(--brand);
            letter-spacing: .2px;
            line-height: 1;
        }

        .header-link,
        .footer-link,
        .megamenu-link {
            color: var(--muted);
            font-size: 14px;
            font-weight: 200;
            transition: color .15s ease;
        }

        .header-link:hover,
        .footer-link:hover,
        .megamenu-link:hover {
            color: var(--brand);
        }

        /* Top nav (bottom underline on hover, tighter paddings) */
        .nav-link {
            font-size: 14px;
            color: #555;
            padding: 10px 0;
            border-bottom: 2px solid transparent;
            transition: color .15s ease, border-color .15s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--ink);
            border-bottom-color: var(--brand);
        }

        /* Search bar segmented control look */
        .search-wrap {
            background: var(--bg-soft);
            border: 1px solid var(--line);
            border-radius: 8px;
            height: 35px;
        }

        .search-btn {
            padding: 0 12px;
            font-size: 14px;
            color: #5f5f5f;
            height: 44px;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .search-input {
            background: transparent;
            border: none;
            height: 44px;
            width: 100%;
            font-size: 14px;
            color: #222;
            padding: 0 12px 0 36px;
        }

        .search-input::placeholder {
            color: #9a9a9a;
        }

        .search-input:focus {
            outline: none;
        }

        /* Megamenu */
        .megamenu {
            display: none;
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            background: #fff;
            border-top: 1px solid var(--line);
            box-shadow: 0 12px 24px rgba(0, 0, 0, .08);
        }

        .megamenu-category-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 600;
            color: #333;
            transition: background .15s ease;
        }

        .megamenu-category-link:hover,
        .megamenu-category-link.active {
            background: #f3f7f7;
        }

        /* Grid */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (min-width:768px) {
            .grid-container {
                grid-template-columns: repeat(3, 1fr);
                gap: 18px;
            }
        }

        @media (min-width:1024px) {
            .grid-container {
                grid-template-columns: repeat(5, 1fr);
                gap: 18px;
            }
        }

        @media (min-width:1280px) {
            .grid-container {
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
            }
        }

        .product-image {
            width: 100%;
            aspect-ratio: 3 / 4;
            /* mobile */
            object-fit: cover;
            border-radius: 10px;
            /* a hair rounder, like screenshot */
            background: #eee;
            display: block;
            transition: transform .2s ease;
        }

        @media (min-width:768px) {
            .product-image {
                aspect-ratio: 2 / 3;
            }

            /* taller on desktop/tablet */
        }

        .fav-badge {
            position: absolute;
            right: 12px;
            bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #666;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .18);
        }

        .fav-badge svg {
            width: 18px;
            height: 18px;
            stroke: #7a7a7a;
            fill: none;
            stroke-width: 1.75;
        }

        .grid-item:hover .product-image {
            transform: scale(1.01);
        }

        /* Card texts tighter to match Used */
        .brand-line {
            font-size: 12px;
            color: #757575;
            margin-top: 6px;
        }

        .meta-line {
            font-size: 11px;
            color: #8a8a8a;
            margin-top: 2px;
        }

        .price-line {
            font-weight: 700;
            font-size: 14px;
            margin-top: 6px;
            color: #111;
        }

        .incl-line {
            font-size: 11px;
            color: #FC0E00;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 2px;
        }

        /* Badge over image (likes) */
        .like-badge {
            position: absolute;
            right: 6px;
            bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, .45);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 999px;
            backdrop-filter: blur(4px);
        }

        /* Sticky header polish */
        #main-header {
            border-bottom: 1px solid var(--line);
            background: #fff;
        }

        /* resources/css/app.css */
        .skeleton {
            background-color: #e2e8f0;
            /* Tailwind's gray-300 */
            position: relative;
            overflow: hidden;
        }

        /* The shimmering effect */
        .skeleton::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: skeleton-shine 1.5s infinite;
        }

        @keyframes skeleton-shine {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }
    </style>
    @if (!empty(config('settings.global_custom_css')))
        <style>
            {!! config('settings.global_custom_css') !!}
        </style>
    @endif

    @include('backend.layouts.partials.integration-scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    {{-- {!! Hook::applyFilters(AdminFilterHook::ADMIN_HEAD, '') !!} --}}

</head>

<body x-data="{
    page: 'ecommerce',
    darkMode: false,
    stickyMenu: false,
    sidebarToggle: $persist(false),
    scrollTop: false
}" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;
$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
$watch('sidebarToggle', value => localStorage.setItem('sidebarToggle', JSON.stringify(value)));

// Add loaded class for smooth fade-in
$nextTick(() => {
    const appContainer = document.querySelector('.app-container');
    if (appContainer) {
        appContainer.classList.add('loaded');
    }
});" :class="{ 'dark bg-gray-900': darkMode === true }">


    @include('layouts.partials.header')

    <main class="py-4">
        @yield('content')
        {{ $slot ?? '' }}
    </main>
    @include('layouts.partials.footer')
    @livewire(\App\Livewire\LoginPopup::class)
    @yield('after_body')
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('login_required')) {
                // Dispatch Livewire event to open the login popup
                Livewire.dispatch('open-login-popup');

                // Clean up the URL
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({
                    path: newUrl
                }, '', newUrl);
            }
        });
    </script>

    @if (!empty(config('settings.global_custom_js')))
        <script>
            {!! config('settings.global_custom_js') !!}
        </script>
    @endif

    @livewireScriptConfig

</body>

</html>
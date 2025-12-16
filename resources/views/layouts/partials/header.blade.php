<header class="sticky top-0 z-50" id="main-header">
    <div class="shell px-4 md:px-6">
        <!-- Top row -->
        <div class="flex items-center justify-between py-2 border-b">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="vinted-brand-text">
                     <img
                        class="dark:hidden max-h-[80px]"
                        src="{{ config('settings.site_logo_lite') ?? asset('images/logo/lara-dashboard.png') }}"
                        alt="{{ config('app.name') }}"
                        style="width: 100px!important;"
                    />
                    <img
                        class="hidden dark:block max-h-[80px]"
                        src="{{ config('settings.site_logo_dark') ?? '/images/logo/lara-dashboard-dark.png' }}"
                        alt="{{ config('app.name') }}"
                        style="width: 100px!important;"
                    />
                </a>
            </div>

            <!-- Search -->
            <div class="hidden md:flex flex-1 max-w-[720px] mx-6 items-center search-wrap">
                <form action="{{ route('search') }}" method="GET" class="w-full flex items-center">
                    <div class="relative">
                        <select name="type" class="appearance-none bg-transparent border-none text-gray-700 py-2 pl-4 pr-8 leading-tight focus:outline-none focus:ring-0 cursor-pointer text-sm font-medium">
                            <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Item</option>
                            <option value="user" {{ request('type') == 'user' ? 'selected' : '' }}>Member</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                    <div class="h-6 w-px bg-[#d9d9d9] mx-2"></div>
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 10-14 0 7 7 0 0014 0z"></path>
                        </svg>
                        <input type="text" name="query" value="{{ request('query') }}" class="search-input pl-10 w-full focus:outline-none" placeholder="Search for items or members">
                    </div>
                    <button type="submit" class="hidden"></button>
                </form>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-5">
                @if (Auth::check())
                    <a href="{{ route('chat.dashboard') }}" class="text-gray-600 hover:text-black" aria-label="Messages">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75">
                            </path>
                        </svg>
                    </a>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-600 hover:text-black relative" aria-label="Notifications">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022 23.848 23.848 0 005.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0">
                                </path>
                            </svg>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg z-50 overflow-hidden" style="display: none;">
                            <div class="py-2">
                                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <a href="{{ $notification->data['url'] ?? '#' }}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                        <p class="text-sm text-gray-800">{{ $notification->data['message'] ?? 'New Notification' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </a>
                                @empty
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">No new notifications</div>
                                @endforelse
                                <div class="bg-gray-50 px-4 py-2 text-center">
                                    <a href="{{ route('notifications.index') }}" class="text-xs text-teal-600 font-semibold hover:underline">View all</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('favorites.index') }}" class="text-gray-600 hover:text-black" aria-label="Favourites">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z">
                            </path>
                        </svg>
                    </a>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition" aria-label="Profile">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->full_name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                        </button>

                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-md shadow-lg z-50 py-2" style="display: none;">
                            <a href="{{ route('users.show', auth()->user()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                            <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Personalisation</a>
                            <a href="{{ route('wallet.index') }}" class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <span>Balance</span>
                                <span class="text-gray-500">£{{ number_format(auth()->user()->wallet?->balance ?? 0, 2) }}</span>
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My orders</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Donations</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Invite friends</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Log out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <button
                        class="px-4 border border-vinted-teal text-vinted-teal font-bold py-2 rounded-md hover:bg-vinted-teal/10 transition-colors text-sm">
                        Login / register
                    </button>
                @endif
                <a href="{{ route('items.create') }}" class="px-4 py-2 rounded text-white font-bold text-sm" style="background:var(--brand)">Sell
                    now</a>
                <a href="#" class="text-gray-600 hover:text-black" aria-label="Help">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z">
                        </path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Bottom nav -->
        <nav class="hidden md:flex items-center justify-center gap-6 relative">
            <a href="#" class="nav-link" data-menu="women">Women</a>
            <a href="#" class="nav-link" data-menu="men">Men</a>
            <a href="#" class="nav-link" data-menu="designer">Designer</a>
            <a href="#" class="nav-link" data-menu="kids">Kids</a>
            <a href="#" class="nav-link" data-menu="home">Home</a>
            <a href="#" class="nav-link" data-menu="electronics">Electronics</a>
            <a href="#" class="nav-link" data-menu="entertainment">Entertainment</a>
            <a href="#" class="nav-link" data-menu="hobbies">Hobbies &amp; Collectables</a>
            <a href="#" class="nav-link" data-menu="sports">Sports</a>
            <a href="#" class="nav-link">About</a>
            <a href="#" class="nav-link">Our Platform</a>
        </nav>
    </div>

    <!-- Megamenus -->
    <div id="megamenus-container" class="relative">
        <div id="men-megamenu" class="megamenu" style="display: none;">
            <div class="shell px-4 md:px-6 py-8 flex gap-10">
                <!-- Left list -->
                <div class="w-1/4">
                    <ul class="space-y-2">
                        <li><a href="#" class="megamenu-category-link active"><svg class="w-5 h-5 text-teal-500"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                    </path>
                                </svg>Recommended</a></li>
                        <li><a href="#" class="megamenu-category-link"><svg class="w-5 h-5 text-teal-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5.52 19c.64-2.2 1.84-3 3.22-3h6.52c1.38 0 2.58.8 3.22 3"></path>
                                    <path d="M12 13a3 3 0 100-6 3 3 0 000 6z"></path>
                                    <path d="M18.74 3.26a8 8 0 11-13.48 0"></path>
                                </svg>Clothing</a></li>
                        <li><a href="#" class="megamenu-category-link"><svg class="w-5 h-5 text-teal-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M7 19v-2.125a2 2 0 01.5-1.414L16 6.961a2 2 0 012.828 0L20.5 8.636a2 2 0 010 2.828L11.5 20.5a2 2 0 01-1.414.5H8a1 1 0 01-1-1z">
                                    </path>
                                </svg>Shoes</a></li>
                        <li><a href="#" class="megamenu-category-link"><svg class="w-5 h-5 text-teal-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>Accessories</a></li>
                        <li><a href="#" class="megamenu-category-link"><svg class="w-5 h-5 text-teal-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M20.57 4.25a1 1 0 00-1.41 0L14 9.42l-2.12-2.12a1 1 0 00-1.42 0L3.43 14.34a1 1 0 000 1.41l1.41 1.41a1 1 0 001.42 0L12 11.24l2.12 2.12a1 1 0 001.42 0l6.02-6.02a1 1 0 000-1.42z">
                                    </path>
                                    <path d="M18 10.42V18a2 2 0 01-2 2H8a2 2 0 01-2-2v-4.59"></path>
                                </svg>Grooming</a></li>
                    </ul>
                </div>
                <!-- Right columns -->
                <div class="w-3/4 grid grid-cols-2 gap-x-8">
                    <ul class="space-y-3">
                        <li><a href="#" class="megamenu-link font-bold">See all</a></li>
                        <li><a href="#" class="megamenu-link">Outerwear</a></li>
                        <li><a href="#" class="megamenu-link">Suits &amp; blazers</a></li>
                        <li><a href="#" class="megamenu-link">Trousers</a></li>
                        <li><a href="#" class="megamenu-link">Socks &amp; underwear</a></li>
                        <li><a href="#" class="megamenu-link">Swimwear</a></li>
                        <li><a href="#" class="megamenu-link">Costumes &amp; special outfits</a></li>
                    </ul>
                    <ul class="space-y-3">
                        <li><a href="#" class="megamenu-link">Jeans</a></li>
                        <li><a href="#" class="megamenu-link">Tops &amp; t-shirts</a></li>
                        <li><a href="#" class="megamenu-link">Jumpers &amp; sweaters</a></li>
                        <li><a href="#" class="megamenu-link">Shorts</a></li>
                        <li><a href="#" class="megamenu-link">Sleepwear</a></li>
                        <li><a href="#" class="megamenu-link">Activewear</a></li>
                        <li><a href="#" class="megamenu-link">Other men's clothing</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Add more megamenus as needed -->
    </div>
</header>
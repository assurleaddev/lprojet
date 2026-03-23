<header x-data="{ mobileMenuOpen: false }" x-init="$watch('mobileMenuOpen', val => { document.body.style.overflow = val ? 'hidden' : ''; document.documentElement.style.overflow = val ? 'hidden' : ''; document.body.classList.toggle('overflow-hidden', val); })" class="sticky top-0 z-[100] bg-white" id="main-header" style="overflow: visible !important;">
    <div class="shell px-4 md:px-6">
        <!-- Top row -->
        <div class="flex items-center justify-between py-2 border-b">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="brand-text">
                    <img class="dark:hidden max-h-[80px]"
                        src="{{ config('settings.site_logo_lite') ?? asset('images/logo/lara-dashboard.png') }}"
                        alt="{{ config('app.name') }}" style="width: 100px!important;" />
                    <img class="hidden dark:block max-h-[80px]"
                        src="{{ config('settings.site_logo_dark') ?? '/images/logo/lara-dashboard-dark.png' }}"
                        alt="{{ config('app.name') }}" style="width: 100px!important;" />
                </a>
            </div>

            <!-- Search -->
            <div class="hidden md:flex flex-1 max-w-[720px] mx-6 items-center search-wrap">
                <x-search-bar />
            </div>

            <!-- Mobile Burger Button -->
            <button @click="mobileMenuOpen = true" class="md:hidden p-2 text-gray-600 hover:text-black focus:outline-none" aria-label="Menu">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Mobile Backdrop -->
            <div x-show="mobileMenuOpen" style="display: none;" @click="mobileMenuOpen = false" x-transition.opacity class="fixed inset-0 bg-black/50 z-[90] md:hidden"></div>

            <!-- Actions (Desktop Row / Mobile Sidebar) -->
            <div :class="{'translate-x-0': mobileMenuOpen, 'translate-x-full': !mobileMenuOpen}" 
                 class="fixed inset-y-0 right-0 z-[100] w-full bg-white shadow-2xl flex flex-col p-6 gap-6 transition-transform duration-300 ease-in-out transform overflow-y-auto md:overflow-visible md:static md:translate-x-0 md:w-auto md:bg-transparent md:shadow-none md:flex-row md:items-center md:p-0 md:gap-5">
                
                <!-- Close Button (Mobile Only) -->
                <div class="flex items-center justify-between md:hidden border-b pb-4 mb-2">
                    <span class="font-bold text-lg text-gray-800">Menu</span>
                    <button @click="mobileMenuOpen = false" class="p-2 text-gray-500 hover:text-black">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                @if (Auth::check())
                                <a href="{{ route('chat.dashboard') }}" class="text-gray-600 hover:text-black relative shrink-0 flex items-center md:block"
                                    aria-label="Messages" x-data="{
                                                                                                            msgCount: {{ auth()->user()->unreadMessagesCount() + auth()->user()->unreadChatNotificationsCount() }},
                                                                                                            chatTypes: {{ \Illuminate\Support\Js::from(\App\Models\User::getChatNotificationTypes()) }},
                                                                                                            init() {
                                                                                                                if (typeof Echo !== 'undefined') {
                                                                                                                    Echo.private('App.Models.User.{{ auth()->id() }}')
                                                                                                                        .notification((notification) => {
                                                                                                                            if (this.chatTypes.includes(notification.type) || (notification.type && notification.type.startsWith('offer_'))) {
                                                                                                                                this.msgCount++;
                                                                                                                            }
                                                                                                                        });
                                                                                                                }
                                                                                                            }
                                                                                                        }">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75">
                                        </path>
                                    </svg>
                                    <span x-show="msgCount > 0" x-text="msgCount"
                                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full z-10">
                                    </span>
                                    <span class="ml-3 font-medium md:hidden">Messages</span>
                                </a>

                                <div class="relative shrink-0 flex flex-col md:block" x-data="{
                                                                                                                                        open: false,
                                                                                                                                        count: {{ auth()->user()->unreadSocialNotificationsCount() }},
                                                                                                                                        chatTypes: {{ \Illuminate\Support\Js::from(\App\Models\User::getChatNotificationTypes()) }},
                                                                                                                                        notifications: {{ \Illuminate\Support\Js::from(auth()->user()->socialNotifications(5)->map(function ($n) {
                        return [
                            'id' => $n->id,
                            'message' => $n->data['message'] ?? 'New Notification',
                            'url' => route('notifications.read', $n->id),
                            'created_at' => $n->created_at->diffForHumans(),
                            'read_at' => $n->read_at
                        ];
                    })) }},
                                                                                                                                        init() {
                                                                                                                                            if (typeof Echo !== 'undefined') {
                                                                                                                                                Echo.private('App.Models.User.{{ auth()->id() }}')
                                                                                                                                                    .notification((notification) => {
                                                                                                                                                        if (!this.chatTypes.includes(notification.type) && !(notification.type && notification.type.startsWith('offer_'))) {
                                                                                                                                                            this.count++;
                                                                                                                                                            this.notifications.unshift({
                                                                                                                                                                id: notification.id,
                                                                                                                                                                message: notification.message,
                                                                                                                                                                url: '/notifications/' + notification.id + '/read',
                                                                                                                                                                created_at: 'Just now',
                                                                                                                                                                read_at: null
                                                                                                                                                            });
                                                                                                                                                            if (this.notifications.length > 5) {
                                                                                                                                                                this.notifications.pop();
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    });
                                                                                                                                            }
                                                                                                                                        }
                                                                                                                                    }">
                                    <button @click="open = !open" class="text-gray-600 hover:text-black relative flex items-center md:block text-left"
                                        aria-label="Notifications">
                                        <div class="relative">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022 23.848 23.848 0 005.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0">
                                                </path>
                                            </svg>
                                            <span x-show="count > 0" x-text="count"
                                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full z-10">
                                            </span>
                                        </div>
                                        <span class="ml-4 font-medium md:hidden">Notifications</span>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                        class="md:absolute md:right-0 mt-2 w-full md:w-80 bg-white border border-gray-200 rounded-md shadow-sm md:shadow-lg z-50 overflow-hidden"
                                        style="display: none;">
                                        <div class="py-2">
                                            <template x-for="notification in notifications" :key="notification.id">
                                                <a :href="notification.url"
                                                    class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 flex items-start justify-between group">
                                                    <div>
                                                        <p class="text-sm text-gray-800" x-text="notification.message"></p>
                                                        <p class="text-xs text-gray-500 mt-1" x-text="notification.created_at"></p>
                                                    </div>
                                                    <div x-show="!notification.read_at"
                                                        class="ml-2 w-2 h-2 rounded-full bg-red-500 flex-shrink-0 mt-1.5"></div>
                                                </a>
                                            </template>

                                            <div x-show="notifications.length === 0"
                                                class="px-4 py-3 text-sm text-gray-500 text-center">
                                                No new notifications
                                            </div>

                                            <div class="bg-gray-50 px-4 py-2 text-center">
                                                <a href="{{ route('notifications.index') }}"
                                                    class="text-xs font-semibold hover:underline" style="color: var(--brand)">View
                                                    all</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('favorites.index') }}" class="text-gray-600 hover:text-black shrink-0 flex items-center md:block" aria-label="Favourites">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z">
                                        </path>
                                    </svg>
                                    <span class="ml-4 font-medium md:hidden">Favorites</span>
                                </a>

                                <div class="relative shrink-0 flex flex-col md:block" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex items-center gap-1 sm:gap-2 hover:opacity-80 transition w-full text-left"
                                        aria-label="Profile">
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->full_name }}"
                                            class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                        <span class="ml-2 font-medium md:hidden">{{ auth()->user()->full_name }}</span>
                                        <svg class="w-4 h-4 text-gray-600 ml-auto md:ml-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                            </path>
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                        class="md:absolute md:right-0 mt-2 w-full md:w-64 bg-white border border-gray-200 rounded-md shadow-sm md:shadow-lg z-50 py-2"
                                        style="display: none;">
                                        <a href="{{ route('vendor.show', auth()->user()) }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                                        <a href="{{ route('settings.profile') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
                                        <a href="{{ route('settings.account') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Personalisation</a>
                                        <a href="{{ route('wallet.index') }}"
                                            class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <span>Balance</span>
                                            <span class="text-gray-500">{{ number_format(auth()->user()->wallet?->balance ?? 0, 2) }}
                                                MAD</span>
                                        </a>
                                        <a href="{{ route('orders.index') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My orders</a>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Log
                                                out</button>
                                        </form>
                                    </div>
                                </div>
                @else
                    <button @click="$dispatch('open-login-popup'); mobileMenuOpen = false"
                        class="px-4 font-bold py-2 rounded-md transition-colors text-sm whitespace-nowrap shrink-0 text-center"
                        style="border: 1px solid var(--brand); color: var(--brand);">
                        Login / register
                    </button>
                @endif
                <a href="{{ route('items.create') }}" class="px-4 py-2 rounded text-white font-bold text-sm whitespace-nowrap shrink-0 text-center"
                    style="background:var(--brand)">Sell now</a>
                <a href="#" class="text-gray-600 hover:text-black shrink-0 flex items-center md:block" aria-label="Help">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z">
                        </path>
                    </svg>
                    <span class="ml-4 font-medium md:hidden">Help Center</span>
                </a>

                <!-- Language Dropdown -->
                <div class="relative shrink-0 flex flex-col md:block" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-600 hover:text-black flex items-center gap-1 w-full text-left"
                        aria-label="Language">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                        <span class="ml-3 font-medium md:hidden inline-block flex-1">Language</span>
                        <svg class="w-4 h-4 ml-auto md:ml-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false"
                        class="md:absolute md:right-0 mt-2 w-full md:w-48 bg-white border border-gray-200 rounded-md shadow-sm md:shadow-lg z-50 py-1"
                        style="display: none;" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 md:scale-95" x-transition:enter-end="opacity-100 md:scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 md:scale-100" x-transition:leave-end="opacity-0 md:scale-95">

                        <a href="#"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between group">
                            <span class="font-medium">English</span>
                            @if(app()->getLocale() == 'en')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="color: var(--brand)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </a>
                        <a href="#"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between group">
                            <span class="font-medium">Français</span>
                            @if(app()->getLocale() == 'fr')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="color: var(--brand)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- Teleport Target for Mobile Categories -->
                <div id="mobile-categories-teleport" class="mt-2 pt-4 border-t border-gray-100 flex-1 overflow-y-auto md:hidden"></div>
            </div>
        </div>

        <!-- Mobile Search Row -->
        <div class="md:hidden py-3 border-b border-gray-100 mb-2 relative z-0">
            <x-search-bar />
        </div>

        <!-- (Moved Bottom nav out of shell) -->
    </div>

    <!-- Bottom nav & Megamenus -->
    <livewire:home-menu />
</header>
<aside class="w-full md:w-64 flex-shrink-0">
    <nav class="space-y-1">
        <a href="{{ route('settings.profile') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.profile') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ __('Profile') }}</a>
        <a href="{{ route('settings.account') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.account') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ __('Account settings') }}</a>
        <a href="{{ route('settings.postage') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.postage') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ __('Shipping') }}</a>
        <a href="{{ route('settings.payments') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.payments') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ __('Payments') }}</a>
        <a href="{{ route('settings.bundle-discounts') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.bundle-discounts') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ __('Bundle discounts') }}</a>
        <a href="{{ route('settings.notifications') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.notifications') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ __('Notifications') }}</a>
        <a href="#" class="block px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50">{{ __('Privacy settings') }}</a>
        <a href="{{ route('settings.security') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.security') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ __('Security') }}</a>
    </nav>
</aside>
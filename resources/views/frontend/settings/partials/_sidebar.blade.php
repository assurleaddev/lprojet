<aside class="w-full md:w-64 flex-shrink-0">
    <nav class="space-y-1">
        <a href="{{ route('settings.profile') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.profile') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Profile
            details</a>
        <a href="{{ route('settings.account') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.account') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Account
            settings</a>
        <a href="{{ route('settings.postage') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.postage') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Postage</a>
        <a href="{{ route('settings.payments') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.payments') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Payments</a>
        <a href="{{ route('settings.bundle-discounts') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.bundle-discounts') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Bundle
            discounts</a>
        <a href="{{ route('settings.notifications') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.notifications') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Notifications</a>
        <a href="#" class="block px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50">Privacy
            settings</a>
        <a href="{{ route('settings.security') }}"
            class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.security') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Security</a>
    </nav>
</aside>
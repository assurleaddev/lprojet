@extends('layouts.app')

@section('title', 'Notification Settings')

@section('content')
    <div class="shell px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Settings</h1>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <nav class="space-y-1">
                    <a href="{{ route('settings.profile') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Profile details</a>
                    <a href="{{ route('settings.account') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Account
                        settings</a>
                    <a href="{{ route('settings.postage') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Postage</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Payments</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Bundle
                        discounts</a>
                    <a href="{{ route('settings.notifications') }}"
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Notifications</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Privacy
                        settings</a>
                    <a href="{{ route('settings.security') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Security</a>
                </nav>
            </aside>

            <!-- Content -->
            <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('settings.notifications.update') }}" method="POST">
                    @csrf

                    <div class="flex items-center justify-between mb-8 pb-8 border-b border-gray-100">
                        <span class="font-medium text-gray-900">Enable email notifications</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enable_email_notifications" value="1" class="sr-only peer" {{ $user->getMeta('enable_email_notifications') ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--brand)]">
                            </div>
                        </label>
                    </div>

                    <!-- News -->
                    <div class="mb-8">
                        <h3 class="text-sm text-gray-500 mb-4">News</h3>
                        <div class="space-y-4 border border-gray-200 rounded-md divide-y divide-gray-100">
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <span class="block font-medium text-gray-900">Used Updates</span>
                                    <span class="block text-xs text-gray-500">Be the first to know about our newest
                                        features, updates, and changes</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notify_vinted_updates" value="1" class="sr-only peer" {{ $user->getMeta('notify_vinted_updates') ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                    </div>
                                </label>
                            </div>
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <span class="block font-medium text-gray-900">Marketing communications</span>
                                    <span class="block text-xs text-gray-500">Receive personalised offers, news, and
                                        recommendations</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notify_marketing" value="1" class="sr-only peer" {{ $user->getMeta('notify_marketing') ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- High-priority notifications -->
                    <div class="mb-8">
                        <h3 class="text-sm text-gray-500 mb-4">High-priority notifications</h3>
                        <div class="space-y-4 border border-gray-200 rounded-md divide-y divide-gray-100">
                            <div class="p-4 flex items-center justify-between">
                                <span class="block font-medium text-gray-900">New messages</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notify_high_priority_messages" value="1"
                                        class="sr-only peer" {{ $user->getMeta('notify_high_priority_messages') ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                    </div>
                                </label>
                            </div>
                            <div class="p-4 flex items-center justify-between">
                                <span class="block font-medium text-gray-900">New feedback</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notify_high_priority_feedback" value="1"
                                        class="sr-only peer" {{ $user->getMeta('notify_high_priority_feedback') ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                    </div>
                                </label>
                            </div>
                            <div class="p-4 flex items-center justify-between">
                                <span class="block font-medium text-gray-900">Reduced items</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notify_high_priority_reduced_items" value="1"
                                        class="sr-only peer" {{ $user->getMeta('notify_high_priority_reduced_items') ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Other notifications -->
                    <div class="mb-8">
                        <h3 class="text-sm text-gray-500 mb-4">Other notifications</h3>
                        <div class="space-y-4 border border-gray-200 rounded-md divide-y divide-gray-100">
                            <div class="p-4 flex items-center justify-between">
                                <span class="block font-medium text-gray-900">Favourited items</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notify_favourited_items" value="1" class="sr-only peer" {{ $user->getMeta('notify_favourited_items') ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                    </div>
                                </label>
                            </div>
                            <div class="p-4 flex items-center justify-between">
                                <span class="block font-medium text-gray-900">New items</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notify_new_items" value="1" class="sr-only peer" {{ $user->getMeta('notify_new_items') ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Limit -->
                    <div class="mb-8">
                        <label class="block text-sm text-gray-500 mb-2">Set a daily limit for each notification type</label>
                        <select name="notification_limit"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-[var(--brand)] focus:ring focus:ring-red-100 focus:ring-opacity-50">
                            <option value="1" {{ $user->getMeta('notification_limit') == '1' ? 'selected' : '' }}>Up to 1
                                notification</option>
                            <option value="2" {{ $user->getMeta('notification_limit') == '2' ? 'selected' : '' }}>Up to 2
                                notifications</option>
                            <option value="5" {{ $user->getMeta('notification_limit') == '5' ? 'selected' : '' }}>Up to 5
                                notifications</option>
                            <option value="unlimited" {{ $user->getMeta('notification_limit') == 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-[var(--brand)] text-white px-6 py-2 rounded font-medium hover:opacity-90">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
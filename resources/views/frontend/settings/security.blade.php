@extends('layouts.app')

@section('title', 'Security Settings')

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
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Account settings</a>
                    <a href="{{ route('settings.postage') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Postage</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Payments</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Bundle
                        discounts</a>
                    <a href="{{ route('settings.notifications') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Notifications</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Privacy
                        settings</a>
                    <a href="{{ route('settings.security') }}"
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Security</a>
                </nav>
            </aside>

            <!-- Content -->
            <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Email Change -->
                <div class="mb-8 pb-8 border-b border-gray-100">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Email</h2>
                    <p class="text-sm text-gray-500 mb-4">Keep your email up to date.</p>

                    <form action="{{ route('settings.security.email.request') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Email</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full bg-gray-100 border border-gray-300 rounded-lg p-2.5 shadow-sm">
                        </div>

                        @if(session('email_verification_sent'))
                            <div class="bg-blue-50 p-4 rounded-md border border-blue-200">
                                <p class="text-sm text-blue-700 mb-2">We sent a verification code to your new email address.</p>
                                <form action="{{ route('settings.security.email.verify') }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="code" placeholder="Enter code"
                                        class="flex-1 border border-gray-300 rounded-lg p-2.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <button type="submit"
                                        class="bg-teal-600 text-white px-6 py-2 rounded font-medium hover:bg-teal-700">Verify</button>
                                </form>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Email</label>
                                <div class="flex gap-2">
                                    <input type="email" name="new_email"
                                        class="flex-1 border border-gray-300 rounded-lg p-2.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <button type="submit"
                                        class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded font-medium hover:bg-gray-50">Change</button>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Password Change -->
                <div class="mb-8 pb-8 border-b border-gray-100">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Password</h2>
                    <p class="text-sm text-gray-500 mb-4">Protect your account with a stronger password.</p>

                    <form action="{{ route('settings.security.password.update') }}" method="POST"
                        class="space-y-4 max-w-md">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password"
                                class="w-full border border-gray-300 rounded-lg p-2.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password"
                                class="w-full border border-gray-300 rounded-lg p-2.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            @error('new_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation"
                                class="w-full border border-gray-300 rounded-lg p-2.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>
                        <button type="submit"
                            class="bg-teal-600 text-white px-6 py-2 rounded font-medium hover:bg-teal-700">Update
                            Password</button>
                    </form>
                </div>

                <!-- 2FA -->
                <div class="mb-8 pb-8 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">2-step verification</h2>
                            <p class="text-sm text-gray-500">Confirm new logins with a code sent to your email.</p>
                        </div>
                        <form action="{{ route('settings.security.2fa.toggle') }}" method="POST">
                            @csrf
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_2fa" value="1" class="sr-only peer"
                                    onchange="this.form.submit()" {{ $user->getMeta('enable_2fa') ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                </div>
                            </label>
                        </form>
                    </div>
                    @if(session('2fa_verification_needed'))
                        <div class="mt-4 bg-blue-50 p-4 rounded-md border border-blue-200">
                            <p class="text-sm text-blue-700 mb-2">To enable 2FA, verify your email address.</p>
                            <form action="{{ route('settings.security.2fa.verify') }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="code" placeholder="Enter code"
                                    class="flex-1 border border-gray-300 rounded-lg p-2.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <button type="submit"
                                    class="bg-teal-600 text-white px-6 py-2 rounded font-medium hover:bg-teal-700">Verify &
                                    Enable</button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Login Activity -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Login activity</h2>
                    <p class="text-sm text-gray-500 mb-4">Manage your logged-in devices.</p>

                    <div class="space-y-4">
                        @foreach($sessions as $session)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-md">
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ $session->ip_address }}
                                        @if($session->id === request()->session()->getId())
                                            <span class="text-teal-600 text-xs ml-2">(Current Device)</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }} ·
                                        {{ $session->user_agent }}
                                    </div>
                                </div>
                                @if($session->id !== request()->session()->getId())
                                    <form action="{{ route('settings.security.logout_session', $session->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-teal-600 hover:underline">Log
                                            out</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
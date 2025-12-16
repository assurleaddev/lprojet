@extends('layouts.app')

@section('title', 'Account Settings')

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
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Account
                        settings</a>
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

                <form action="{{ route('settings.account.update') }}" method="POST">
                    @csrf

                    <!-- Email -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">{{ $user->email }}</h3>
                            <span class="text-xs text-gray-500 flex items-center gap-1">Verified <svg
                                    class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg></span>
                        </div>
                        <button type="button"
                            class="text-vinted-teal border border-vinted-teal px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">Change</button>
                    </div>

                    <!-- Phone number -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-base font-medium text-gray-900">Phone number</h3>
                            <button type="button"
                                class="text-vinted-teal border border-vinted-teal px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">Verify</button>
                        </div>
                        <p class="text-xs text-gray-500">Your phone number will only be used to help you log in. It won't be
                            made public, or used for marketing purposes.</p>
                    </div>

                    <!-- Personal Information -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <label class="text-base font-medium text-gray-900 w-1/3">Full name</label>
                            <div class="w-2/3 text-gray-700 font-medium uppercase">
                                {{ $user->full_name }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-6">
                            <label class="text-base font-medium text-gray-900 w-1/3">Gender</label>
                            <div class="w-2/3">
                                <select name="gender"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-teal-500 focus:border-teal-500">
                                    <option value="" disabled {{ !$user->getMeta('gender') ? 'selected' : '' }}>Select
                                        gender</option>
                                    <option value="Male" {{ $user->getMeta('gender') == 'Male' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="Female" {{ $user->getMeta('gender') == 'Female' ? 'selected' : '' }}>Female
                                    </option>
                                    <option value="Other" {{ $user->getMeta('gender') == 'Other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-base font-medium text-gray-900 w-1/3">Birthday</label>
                            <div class="w-2/3">
                                <input type="date" name="birthday" value="{{ $user->getMeta('birthday') }}"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-teal-500 focus:border-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Holiday mode -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <label class="text-base font-medium text-gray-900">Holiday mode</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="holiday_mode" value="1" class="sr-only peer" {{ $user->getMeta('holiday_mode') ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                            </div>
                        </label>
                    </div>

                    <!-- Social Accounts -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-medium text-gray-900">Facebook</h3>
                            <button type="button"
                                class="text-vinted-teal border border-vinted-teal px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">Link</button>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-base font-medium text-gray-900">Google</h3>
                            <button type="button"
                                class="text-gray-400 border border-gray-300 px-4 py-2 rounded text-sm font-medium cursor-not-allowed"
                                disabled>Linked</button>
                        </div>
                        <p class="text-xs text-gray-500">Link to your other accounts to become a trusted, verified member.
                        </p>
                    </div>

                    <!-- Change password -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-medium text-gray-900">Change password</h3>
                        <button type="button"
                            class="text-vinted-teal border border-vinted-teal px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">Change</button>
                    </div>

                    <!-- Delete account -->
                    <div class="mb-8 flex items-center justify-between cursor-pointer hover:bg-gray-50 p-2 -mx-2 rounded">
                        <h3 class="text-base font-medium text-gray-900">Delete my account</h3>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-teal-600 text-white px-6 py-2 rounded font-medium hover:bg-teal-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="shell px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Settings</h1>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <nav class="space-y-1">
                    <a href="{{ route('settings.profile') }}"
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Profile details</a>
                    <a href="{{ route('settings.account') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Account
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

                <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Your photo -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Your photo</h3>
                        </div>
                        <div class="flex items-center gap-4">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}"
                                class="w-12 h-12 rounded-full object-cover">
                            <label
                                class="cursor-pointer bg-white border border-vinted-teal text-vinted-teal px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">
                                Choose photo
                                <input type="file" name="avatar" class="hidden" accept="image/*"
                                    onchange="document.querySelector('img[alt=\'{{ $user->username }}\']').src = window.URL.createObjectURL(this.files[0])">
                            </label>
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Username</h3>
                        </div>
                        <div class="flex items-center gap-4 w-1/2">
                            <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-vinted-teal focus:ring focus:ring-vinted-teal focus:ring-opacity-50">
                        </div>
                    </div>

                    <!-- About you -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex flex-col md:flex-row md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-base font-medium text-gray-900">About you</h3>
                        </div>
                        <div class="w-full md:w-1/2">
                            <textarea name="about" rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-vinted-teal focus:ring focus:ring-vinted-teal focus:ring-opacity-50"
                                placeholder="Tell us more about yourself and your style">{{ old('about', $user->getMeta('about')) }}</textarea>
                        </div>
                    </div>

                    <!-- My location -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <h3 class="text-sm text-gray-500 mb-4 uppercase tracking-wide">My location</h3>

                        <div class="flex items-center justify-between mb-4">
                            <label class="text-base font-medium text-gray-900">Country</label>
                            <div class="w-1/2">
                                <select name="country"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-vinted-teal focus:ring focus:ring-vinted-teal focus:ring-opacity-50">
                                    <option value="United Kingdom" {{ old('country', $user->getMeta('country')) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="France" {{ old('country', $user->getMeta('country')) == 'France' ? 'selected' : '' }}>France</option>
                                    <option value="USA" {{ old('country', $user->getMeta('country')) == 'USA' ? 'selected' : '' }}>USA</option>
                                    <option value="Morocco" {{ old('country', $user->getMeta('country')) == 'Morocco' ? 'selected' : '' }}>Morocco</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <label class="text-base font-medium text-gray-900">Town/City</label>
                            <div class="w-1/2">
                                <input type="text" name="city" value="{{ old('city', $user->getMeta('city')) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-vinted-teal focus:ring focus:ring-vinted-teal focus:ring-opacity-50"
                                    placeholder="Select a city">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-base font-medium text-gray-900">Show city in profile</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_city" value="1" class="sr-only peer" {{ old('show_city', $user->getMeta('show_city')) ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Language -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Language</h3>
                        </div>
                        <div class="w-1/2">
                            <select name="language"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-vinted-teal focus:ring focus:ring-vinted-teal focus:ring-opacity-50">
                                <option value="en" {{ old('language', $user->getMeta('language')) == 'en' ? 'selected' : '' }}>English, UK (English)</option>
                                <option value="fr" {{ old('language', $user->getMeta('language')) == 'fr' ? 'selected' : '' }}>French (Français)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-teal-600 text-white px-6 py-2 rounded font-medium hover:bg-teal-700">Update
                            profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
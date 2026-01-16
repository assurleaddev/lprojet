@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-center py-12 bg-gray-100 dark:bg-gray-900 sm:px-6 lg:px-8">
        <div
            class="relative w-full max-w-md p-6 bg-white rounded-xl shadow-xl dark:bg-gray-800 my-8 overflow-y-auto max-h-screen">

            <!-- Header -->
            <h2 class="mb-6 text-xl font-bold text-center text-gray-900 dark:text-white">
                {{ __('Inscription') }}
            </h2>

            <form method="POST" action="{{ route('register') }}" data-prevent-unsaved-changes class="space-y-4 text-left">
                @csrf

                <!-- First Name -->
                <div>
                    <input id="first_name" type="text"
                        class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2 @error('first_name') border-red-500 @enderror"
                        name="first_name" value="{{ old('first_name') }}" required autocomplete="first_name" autofocus
                        placeholder="{{ __('Prénom') }}">

                    @error('first_name')
                        <span class="text-xs text-red-500 mt-1 block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <input id="last_name" type="text"
                        class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2 @error('last_name') border-red-500 @enderror"
                        name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name"
                        placeholder="{{ __('Nom') }}">

                    @error('last_name')
                        <span class="text-xs text-red-500 mt-1 block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Email Address -->
                <div>
                    <input id="email" type="email"
                        class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2 @error('email') border-red-500 @enderror"
                        name="email" value="{{ old('email') }}" required autocomplete="email"
                        placeholder="{{ __('Adresse e-mail') }}">

                    @error('email')
                        <span class="text-xs text-red-500 mt-1 block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Password -->
                <div x-data="{ show: false }" class="relative">
                    <input :type="show ? 'text' : 'password'" id="password"
                        class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2 pr-10 @error('password') border-red-500 @enderror"
                        name="password" required autocomplete="new-password" placeholder="{{ __('Mot de passe') }}">

                    <button type="button" @click="show = !show"
                        class="absolute right-0 text-gray-400 top-2 hover:text-gray-600">
                        <iconify-icon :icon="show ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-5 h-5"></iconify-icon>
                    </button>

                    @error('password')
                        <span class="text-xs text-red-500 mt-1 block">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div x-data="{ show: false }" class="relative">
                    <input :type="show ? 'text' : 'password'" id="password-confirm"
                        class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2 pr-10"
                        name="password_confirmation" required autocomplete="new-password"
                        placeholder="{{ __('Confirmer le mot de passe') }}">

                    <button type="button" @click="show = !show"
                        class="absolute right-0 text-gray-400 top-2 hover:text-gray-600">
                        <iconify-icon :icon="show ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-5 h-5"></iconify-icon>
                    </button>
                </div>

                <x-recaptcha page="registration" />

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3 mt-6 text-base font-bold text-white transition-colors bg-teal-700 rounded-md hover:bg-teal-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ __('S\'inscrire') }}
                </button>

                <div class="mt-4 text-center">
                    <a class="text-sm text-teal-600 hover:underline" href="{{ route('login') }}">
                        {{ __('Déjà un compte ? Se connecter') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
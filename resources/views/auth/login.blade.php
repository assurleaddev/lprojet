@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-center py-12 bg-gray-100 dark:bg-gray-900 sm:px-6 lg:px-8">
        <div class="relative w-full max-w-md p-6 bg-white rounded-xl shadow-xl dark:bg-gray-800">

            <!-- Header -->
            <h2 class="mb-6 text-xl font-bold text-center text-gray-900 dark:text-white">
                {{ __('Se connecter') }}
            </h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-6 text-left">
                @csrf

                <!-- Email Address -->
                <div>
                    <input id="email" type="email"
                        class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 bg-transparent px-0 py-2 @error('email') border-red-500 @enderror"
                        style="border-bottom-color: var(--brand)" name="email" value="{{ old('email') }}" required
                        autocomplete="email" autofocus placeholder="{{ __('Identifiant ou adresse email') }}">

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
                        name="password" required autocomplete="current-password" placeholder="{{ __('Mot de passe') }}">

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

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500" type="checkbox"
                        name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="ml-2 block text-sm text-gray-900 dark:text-gray-300" for="remember">
                        {{ __('Se souvenir de moi') }}
                    </label>
                </div>

                <x-recaptcha page="login" />

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3 text-base font-bold text-white transition-colors rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                    style="background-color: var(--brand)">
                    {{ __('Se connecter') }}
                </button>

                <!-- Forgot Password -->
                @if (Route::has('password.request'))
                    <div class="text-center mt-4">
                        <a class="text-sm hover:underline" style="color: var(--brand)" href="{{ route('password.request') }}">
                            {{ __('Mot de passe oublié ?') }}
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
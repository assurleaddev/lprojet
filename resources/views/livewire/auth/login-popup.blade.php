<div x-data="{ open: @entangle('open') }" x-show="open" style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">

    <div @click.away="open = false"
        class="relative w-full max-w-md p-6 bg-white rounded-xl shadow-xl dark:bg-gray-800 transform transition-all"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <!-- Close Button -->
        <button wire:click="closePopup" @click="open = false"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Content -->
        <div class="mt-2 text-center">

            <!-- MENU VIEW -->
            @if($view === 'menu')
                <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">
                    {{ __('Rejoins le mouvement de la seconde main et vends sans frais !') }}
                </h2>
                <p class="hidden mb-8 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Join the second-hand movement and sell for free!') }}
                </p>

                <div class="space-y-3">
                    <!-- Google -->
                    <a href="{{ route('auth.social.redirect', 'google') }}"
                        class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition duration-150">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5 mr-3" alt="Google">
                        {{ __('Continuer avec Google') }}
                    </a>

                    <!-- Apple -->
                    <a href="{{ route('auth.social.redirect', 'apple') }}"
                        class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition duration-150">
                        <svg class="w-5 h-5 mr-3 text-black dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.74 1.18 0 2.45-1.64 3.98-1.64 1.57 0 3.36.81 4.31 2.37-3.72 1.99-3.08 7.33.65 8.94-.67 1.64-1.59 3.23-2.82 4.41l.05.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z" />
                        </svg>
                        {{ __('Continuer avec Apple') }}
                    </a>

                    <!-- Facebook -->
                    <a href="{{ route('auth.social.redirect', 'facebook') }}"
                        class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition duration-150">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.791-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        {{ __('Continuer avec Facebook') }}
                    </a>
                </div>

                <div class="mt-8 space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Ou inscris-toi avec') }} <button wire:click="setView('register')"
                            class="font-medium text-teal-600 hover:underline hover:text-teal-500">{{ __('ton adresse e-mail') }}</button>
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Tu as déjà un compte ?') }} <button wire:click="setView('login_menu')"
                            class="font-medium text-teal-600 hover:underline hover:text-teal-500">{{ __('Se connecter') }}</button>
                    </p>
                </div>
            @endif

            <!-- LOGIN MENU VIEW -->
            @if($view === 'login_menu')
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">
                    {{ __('Bienvenue !') }}
                </h2>

                <div class="space-y-3">
                    <!-- Google -->
                    <a href="{{ route('auth.social.redirect', 'google') }}"
                        class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition duration-150">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5 mr-3" alt="Google">
                        {{ __('Continuer avec Google') }}
                    </a>

                    <!-- Apple -->
                    <a href="{{ route('auth.social.redirect', 'apple') }}"
                        class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition duration-150">
                        <svg class="w-5 h-5 mr-3 text-black dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.74 1.18 0 2.45-1.64 3.98-1.64 1.57 0 3.36.81 4.31 2.37-3.72 1.99-3.08 7.33.65 8.94-.67 1.64-1.59 3.23-2.82 4.41l.05.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z" />
                        </svg>
                        {{ __('Continuer avec Apple') }}
                    </a>

                    <!-- Facebook -->
                    <a href="{{ route('auth.social.redirect', 'facebook') }}"
                        class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition duration-150">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.791-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        {{ __('Continuer avec Facebook') }}
                    </a>
                </div>

                <div class="mt-8 space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Ou connecte-toi avec') }} <button wire:click="setView('login')"
                            class="font-medium text-teal-600 hover:underline hover:text-teal-500">{{ __('ton e-mail') }}</button>
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Tu n\'as pas de compte ' . config('app.name') . ' ?') }} <button
                            wire:click="setView('register')"
                            class="font-medium text-teal-600 hover:underline hover:text-teal-500">{{ __('S\'inscrire') }}</button>
                    </p>
                </div>
            @endif

            <!-- REGISTER VIEW -->
            @if($view === 'register')
                <h2 class="mb-6 text-xl font-bold text-center text-gray-900 dark:text-white">
                    {{ __('Inscris-toi avec ton email') }}
                </h2>

                <form wire:submit.prevent="register" class="space-y-4 text-left">
                    <!-- Username -->
                    <div>
                        <input type="text" wire:model.blur="username"
                            class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2"
                            placeholder="{{ __('Nom d\'utilisateur') }}">
                        @error('username') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Utilise des lettres, des chiffres ou les deux. Les autres membres verront ce nom sur ton compte.') }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <input type="email" wire:model.blur="email"
                            class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2"
                            placeholder="{{ __('Email') }}">
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Saisis l\'adresse e-mail que tu souhaites utiliser.') }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" wire:model.blur="password"
                            class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2 pr-10"
                            placeholder="{{ __('Mot de passe') }}">
                        <button type="button" @click="show = !show"
                            class="absolute right-0 text-gray-400 top-2 hover:text-gray-600">
                            <iconify-icon :icon="show ? 'heroicons:eye-slash' : 'heroicons:eye'"
                                class="w-5 h-5"></iconify-icon>
                        </button>
                        @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        <p class="mt-1 text-xs text-gray-500">{{ __('Saisis au moins 7 caractères.') }}</p>
                    </div>

                    <!-- Checkboxes -->
                    <div class="pt-4 space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="newsletter"
                                class="mt-1 text-teal-600 border-gray-300 rounded focus:ring-teal-500 w-5 h-5">
                            <span
                                class="text-sm text-gray-500">{{ __('Je souhaite recevoir par e-mail des offres personnalisées et les dernières mises à jour.') }}</span>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="terms"
                                class="mt-1 text-teal-600 border-gray-300 rounded focus:ring-teal-500 w-5 h-5">
                            <span class="text-sm text-gray-500">
                                {!! __('En t\'inscrivant, tu confirmes que tu acceptes les <a href="#" class="text-teal-600 underline">Termes & Conditions</a>, avoir lu la <a href="#" class="text-teal-600 underline">Politique de confidentialité</a> et avoir au moins 18 ans.') !!}
                            </span>
                        </label>
                        @error('terms') <span class="block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="w-full py-3 mt-6 text-base font-bold text-white transition-colors bg-teal-700 rounded-md hover:bg-teal-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>{{ __('Continuer') }}</span>
                        <span wire:loading>{{ __('Traitement...') }}</span>
                    </button>

                    <div class="mt-4 text-center">
                        <a href="#" class="text-sm text-teal-600 hover:underline">{{ __('Besoin d\'aide ?') }}</a>
                    </div>
                </form>
            @endif

            <!-- LOGIN VIEW -->
            @if($view === 'login')
                <h2 class="mb-6 text-xl font-bold text-center text-gray-900 dark:text-white">
                    {{ __('Se connecter') }}
                </h2>

                <form wire:submit.prevent="login" class="space-y-6 text-left">
                    <!-- Identifier -->
                    <div>
                        <input type="text" wire:model.blur="login_identifier"
                            class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2"
                            placeholder="{{ __('Identifiant ou adresse email') }}">
                        @error('login_identifier') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" wire:model.blur="login_password"
                            class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2 pr-10"
                            placeholder="{{ __('Mot de passe') }}">
                        <button type="button" @click="show = !show"
                            class="absolute right-0 text-gray-400 top-2 hover:text-gray-600">
                            <iconify-icon :icon="show ? 'heroicons:eye-slash' : 'heroicons:eye'"
                                class="w-5 h-5"></iconify-icon>
                        </button>
                        @error('login_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="w-full py-3 mt-6 text-base font-bold text-white transition-colors bg-teal-700 rounded-md hover:bg-teal-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>{{ __('Continuer') }}</span>
                        <span wire:loading>{{ __('Traitement...') }}</span>
                    </button>

                    <div class="mt-6 space-y-2 text-center">
                        <div>
                            <button type="button" wire:click="setView('forgot_password')"
                                class="text-sm text-teal-600 hover:underline">{{ __('Mot de passe oublié ?') }}</button>
                        </div>
                        <div>
                            <a href="#" class="text-sm text-teal-600 hover:underline">{{ __('Un problème ?') }}</a>
                        </div>
                    </div>
                </form>
            @endif

            <!-- FORGOT PASSWORD VIEW -->
            @if($view === 'forgot_password')
                <h2 class="mb-6 text-xl font-bold text-center text-gray-900 dark:text-white">
                    {{ __('Réinitialise ton mot de passe') }}
                </h2>

                <form wire:submit.prevent="sendResetLink" class="space-y-6 text-left">

                    @if($status)
                        <div class="p-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800"
                            role="alert">
                            {{ $status }}
                        </div>
                    @endif

                    <!-- Email -->
                    <div>
                        <input type="email" wire:model.blur="forgot_email"
                            class="w-full text-base placeholder-gray-500 border-0 border-b border-gray-300 focus:ring-0 focus:border-vinted-teal bg-transparent px-0 py-2"
                            placeholder="{{ __('Entre ton adresse email') }}">
                        @error('forgot_email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="w-full py-3 mt-6 text-base font-bold text-white transition-colors bg-teal-700 rounded-md hover:bg-teal-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>{{ __('Envoyer le lien de réinitialisation') }}</span>
                        <span wire:loading>{{ __('Envoi...') }}</span>
                    </button>

                    <div class="mt-4 text-center">
                        <button type="button" wire:click="setView('login')"
                            class="text-sm text-teal-600 hover:underline font-medium">{{ __('Retour à la connexion') }}</button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</div>
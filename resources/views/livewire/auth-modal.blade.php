<div>
    @if ($modalOpen)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 transition-opacity"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Modal Content -->
        <div
            class="relative w-full max-w-sm p-8 mx-4 bg-white rounded-lg shadow-xl transition-transform transform"
        >
            <!-- Close Button -->
            <button wire:click="$set('modalOpen', false)" class="absolute text-gray-500 top-4 right-4 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- View: Initial Signup with Socials -->
            @if ($view === 'signup-social')
                <div class="text-center">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Rejoignez et vendez vos vêtements d'occasion sans frais</h2>
                    {{-- Social buttons... --}}
                    <div class="space-y-3">
                        {{-- ... --}}
                    </div>
                    <p class="mt-6 text-sm text-gray-600">
                        Ou s'inscrire avec un
                        <button wire:click="setView('signup-email')" class="font-semibold hover:underline text-gray-900">e-mail</button>
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        Déjà un compte ?
                        <button wire:click="setView('login-social')" class="font-semibold hover:underline text-gray-900">Se connecter</button>
                    </p>
                </div>
            @endif

            <!-- View: Sign up with Email -->
            @if ($view === 'signup-email')
                <div class="text-gray-700">
                    <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">S'inscrire avec un e-mail</h2>
                    <form wire:submit.prevent="register" class="space-y-4">
                        <div>
                            <label for="username" class="text-sm font-semibold">Nom d'utilisateur</label>
                            <input type="text" id="username" wire:model.defer="username" class="w-full p-2 mt-1 border rounded-md @error('username') border-red-500 @else border-gray-300 @enderror" >
                            @error('username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email-signup" class="text-sm font-semibold">E-mail</label>
                            <input type="email" id="email-signup" wire:model.defer="email" class="w-full p-2 mt-1 border rounded-md @error('email') border-red-500 @else border-gray-300 @enderror" >
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                             <label for="password-signup" class="text-sm font-semibold">Mot de passe</label>
                             <input type="password" id="password-signup" wire:model.defer="password" class="w-full p-2 mt-1 border rounded-md @error('password') border-red-500 @else border-gray-300 @enderror" >
                             <p class="mt-1 text-xs text-gray-500">Au moins 7 caractères, dont 1 lettre et 1 chiffre</p>
                             @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="pt-2 space-y-4">
                            <label class="flex items-start">
                                <input type="checkbox" wire:model.defer="offersAccepted" class="w-5 h-5 mt-0.5 border-gray-300 rounded focus:ring-0 text-gray-900">
                                <span class="ml-2 text-sm text-gray-600">Je souhaite recevoir des offres personnalisées et être informé(e) en avant-première des dernières nouveautés de Used par e-mail.</span>
                            </label>
                            <label class="flex items-start">
                                <input type="checkbox" wire:model.defer="termsAccepted" class="w-5 h-5 mt-0.5 border-gray-300 rounded focus:ring-0 @error('termsAccepted') border-red-500 @enderror text-gray-900">
                                <span class="ml-2 text-sm text-gray-600">En m'inscrivant, je confirme que j'accepte les <a href="{{ route('cgu') }}" class="font-semibold hover:underline text-gray-900">Conditions Générales d'Utilisation</a>, que j'ai lu la <a href="{{ route('mentions-legales') }}" class="font-semibold hover:underline text-gray-900">Politique de confidentialité</a>, et que j'ai au moins 18 ans.</span>
                            </label>
                             @error('termsAccepted') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full py-3 font-bold text-white rounded-md transition-colors bg-gray-900 hover:bg-gray-800">Continuer</button>
                    </form>
                </div>
            @endif

            <!-- View: Initial Login with Socials -->
            @if ($view === 'login-social')
                <div class="text-center">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Bon retour !</h2>
                     {{-- Social buttons... --}}
                    <div class="space-y-3">
                         {{-- ... --}}
                    </div>
                    <p class="mt-6 text-sm text-gray-600">
                        Ou se connecter avec un
                        <button wire:click="setView('login-email')" class="font-semibold hover:underline text-gray-900">e-mail</button>
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        Pas encore de compte ?
                        <button wire:click="setView('signup-social')" class="font-semibold hover:underline text-gray-900">S'inscrire</button>
                    </p>
                </div>
            @endif

            <!-- View: Log in with Email -->
            @if ($view === 'login-email')
                <div class="text-gray-700">
                    <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">Se connecter</h2>
                    <form wire:submit.prevent="login" class="space-y-4">
                        <div>
                            <label for="email-login" class="text-sm font-semibold">E-mail ou nom d'utilisateur</label>
                            <input type="text" id="email-login" wire:model.defer="email" class="w-full p-2 mt-1 border rounded-md @error('email') border-red-500 @else border-gray-300 @enderror">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password-login" class="text-sm font-semibold">Mot de passe</label>
                            <input type="password" id="password-login" wire:model.defer="password" class="w-full p-2 mt-1 border rounded-md @error('password') border-red-500 @else border-gray-300 @enderror">
                             @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 font-bold text-white rounded-md transition-colors bg-gray-900 hover:bg-gray-800">Continuer</button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
    @endif
</div>

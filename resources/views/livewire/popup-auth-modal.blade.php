<div>
    @if ($modalOpen)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity"
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
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Join and sell pre-loved clothes with no fees</h2>
                    {{-- Social buttons... --}}
                    <div class="space-y-3">
                        {{-- ... --}}
                    </div>
                    <p class="mt-6 text-sm text-gray-600">
                        Or register with
                        <button wire:click="setView('signup-email')" class="font-semibold text-teal-600 hover:underline">email</button>
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        Already have an account?
                        <button wire:click="setView('login-social')" class="font-semibold text-teal-600 hover:underline">Log in</button>
                    </p>
                </div>
            @endif

            <!-- View: Sign up with Email -->
            @if ($view === 'signup-email')
                <div class="text-gray-700">
                    <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">Sign up with email</h2>
                    <form wire:submit.prevent="register" class="space-y-4">
                        <div>
                            <label for="username" class="text-sm font-semibold">Username</label>
                            <input type="text" id="username" wire:model.defer="username" class="w-full p-2 mt-1 border rounded-md @error('username') border-red-500 @else border-gray-300 @enderror" >
                            @error('username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email-signup" class="text-sm font-semibold">Email</label>
                            <input type="email" id="email-signup" wire:model.defer="email" class="w-full p-2 mt-1 border rounded-md @error('email') border-red-500 @else border-gray-300 @enderror" >
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                             <label for="password-signup" class="text-sm font-semibold">Password</label>
                             <input type="password" id="password-signup" wire:model.defer="password" class="w-full p-2 mt-1 border rounded-md @error('password') border-red-500 @else border-gray-300 @enderror" >
                             <p class="mt-1 text-xs text-gray-500">At least 7 characters, including 1 letter and 1 number</p>
                             @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="pt-2 space-y-4">
                            <label class="flex items-start">
                                <input type="checkbox" wire:model.defer="offersAccepted" class="w-5 h-5 mt-0.5 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                                <span class="ml-2 text-sm text-gray-600">I'd like to receive personalised offers and be the first to know about the latest updates to {{ config('app.name') }} via email.</span>
                            </label>
                            <label class="flex items-start">
                                <input type="checkbox" wire:model.defer="termsAccepted" class="w-5 h-5 mt-0.5 text-teal-600 border-gray-300 rounded focus:ring-teal-500 @error('termsAccepted') border-red-500 @enderror">
                                <span class="ml-2 text-sm text-gray-600">By registering, I confirm that I accept <a href="#" class="font-semibold text-teal-600 hover:underline">{{ config('app.name') }}'s Terms and Conditions</a>, have read the <a href="#" class="font-semibold text-teal-600 hover:underline">Privacy Policy</a>, and am at least 18 years old.</span>
                            </label>
                             @error('termsAccepted') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full py-3 font-bold text-white bg-teal-600 rounded-md hover:bg-teal-700">Continue</button>
                    </form>
                </div>
            @endif
            
            <!-- View: Initial Login with Socials -->
            @if ($view === 'login-social')
                <div class="text-center">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Welcome back!</h2>
                     {{-- Social buttons... --}}
                    <div class="space-y-3">
                         {{-- ... --}}
                    </div>
                    <p class="mt-6 text-sm text-gray-600">
                        Or log in with
                        <button wire:click="setView('login-email')" class="font-semibold text-teal-600 hover:underline">email</button>
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        Don't have an account yet?
                        <button wire:click="setView('signup-social')" class="font-semibold text-teal-600 hover:underline">Sign up</button>
                    </p>
                </div>
            @endif

            <!-- View: Log in with Email -->
            @if ($view === 'login-email')
                <div class="text-gray-700">
                    <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">Log in</h2>
                    <form wire:submit.prevent="login" class="space-y-4">
                        <div>
                            <label for="email-login" class="text-sm font-semibold">Email or username</label>
                            <input type="text" id="email-login" wire:model.defer="email" class="w-full p-2 mt-1 border rounded-md @error('email') border-red-500 @else border-gray-300 @enderror">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password-login" class="text-sm font-semibold">Password</label>
                            <input type="password" id="password-login" wire:model.defer="password" class="w-full p-2 mt-1 border rounded-md @error('password') border-red-500 @else border-gray-300 @enderror">
                             @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 font-bold text-white bg-teal-600 rounded-md hover:bg-teal-700">Continue</button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
    @endif
</div>
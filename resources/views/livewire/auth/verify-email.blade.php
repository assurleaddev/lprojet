<div class="flex flex-col items-center justify-center min-h-[60vh] py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg px-4 space-y-8 text-center sm:px-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Vérification de l\'adresse e-mail') }}
            </h2>
            <div class="mt-4 text-[15px] leading-relaxed text-gray-600 dark:text-gray-400">
                <p>{{ __('Saisis le code de vérification qui t\'a été envoyé à') }}</p>
                <p>{{ __('cette adresse e-mail :') }}</p>
                <p class="mt-1 text-gray-900 dark:text-white">{{ auth()->user()->email }}</p>
            </div>
        </div>

        @if($message)
            <div class="p-3 text-sm text-green-700 bg-green-100 rounded-md dark:bg-green-900/50 dark:text-green-300">
                {{ $message }}
            </div>
        @endif

        <form wire:submit.prevent="verify" class="mt-10">
            <div class="relative group">
                <label for="code" class="sr-only">{{ __('Code de vérification') }}</label>
                <input id="code" type="text" wire:model="code" maxlength="4"
                    class="block w-full py-2 text-center text-gray-900 bg-transparent border-0 border-b border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-teal-500 placeholder-gray-400 text-lg"
                    placeholder="{{ __('Saisis le code de vérification ici') }}">
                @error('code') <div class="mt-2 text-sm text-red-500">{{ $message }}</div> @enderror
            </div>

            <div class="mt-8">
                <button type="submit"
                    class="w-full px-4 py-2.5 text-[15px] font-medium text-white transition-colors bg-[#007782] rounded-md hover:bg-[#00666f] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#007782]">
                    {{ __('Vérifier') }}
                </button>
            </div>
        </form>

        <div class="space-y-3 text-[14px]">
            <div>
                <button wire:click="resend" class="font-medium text-[#007782] hover:underline">
                    {{ __('Tu n\'as pas reçu d\'e-mail ?') }}
                </button>
            </div>
            <div>
                <a href="#" class="font-medium text-[#007782] hover:underline">
                    {{ __('Des questions ?') }}
                </a>
            </div>
        </div>
    </div>
</div>
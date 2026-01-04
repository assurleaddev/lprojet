<div class="w-full max-w-lg px-4 mx-auto text-center sm:px-0">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
        {{ __('Saisis le code à 6 chiffres') }}
    </h2>

    <div class="mb-8 text-[15px] leading-relaxed text-gray-600 dark:text-gray-400">
        <p>
            {{ __('Nous l\'avons envoyé au') }} <strong>{{ auth()->user()->phone_country_code }}
                {{ auth()->user()->phone_number }}</strong>.
        </p>
    </div>

    <form wire:submit.prevent="verify" class="space-y-6">
        <div class="relative">
            <input type="text" wire:model="code"
                class="block w-full py-2.5 px-3text-gray-900 bg-transparent border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-teal-600 peer text-center text-xl tracking-widest placeholder-transparent"
                placeholder="000000" maxlength="6" />
            <label
                class="absolute text-sm text-gray-500 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] peer-focus:left-0 peer-focus:text-teal-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 left-0 w-full text-center">
                {{ __('Code') }}
            </label>
        </div>
        @error('code') <div class="text-sm text-center text-red-500">{{ $message }}</div> @enderror

        <div class="mt-8">
            <button type="submit"
                class="w-full px-4 py-3 text-[15px] font-bold text-white transition-colors bg-[#007782] rounded-md hover:bg-[#00666f]">
                {{ __('Vérifier') }}
            </button>
        </div>
    </form>

    <div class="mt-6 text-[14px]">
        <button wire:click="resend"
            class="font-medium text-[#007782] hover:underline bg-transparent border-none cursor-pointer">
            {{ __('Renvoyer un nouveau code') }}
        </button>
    </div>
</div>
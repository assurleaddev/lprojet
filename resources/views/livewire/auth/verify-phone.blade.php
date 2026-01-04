<div>
    <div class="w-full max-w-lg px-4 mx-auto text-center sm:px-0">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            {{ __('Vérifie ton numéro de téléphone') }}
        </h2>

        <div class="mb-8 text-[15px] leading-relaxed text-gray-600 dark:text-gray-400">
            <p>
                {{ __('Nous allons t\'envoyer un message de confirmation ou te téléphoner pour vérifier qu\'il s\'agit bien de ton numéro.') }}
            </p>
        </div>

        @if(session()->has('message'))
            <div class="mb-6 p-3 text-sm text-green-700 bg-green-100 rounded-md dark:bg-green-900/50 dark:text-green-300">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="send" class="space-y-6">
            <div x-data="{
                init() {
                    const input = this.$refs.phoneInput;
                    const iti = window.intlTelInput(input, {
                        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js',
                        separateDialCode: true,
                        initialCountry: 'fr', // Default to France or logic to detect
                        preferredCountries: ['fr', 'us', 'gb', 'be', 'ch', 'ca'],
                    });

                    // Set initial value if present
                    if (@this.country_code && @this.phone_number) {
                        iti.setNumber(@this.country_code + @this.phone_number);
                    } else if (@this.country_code) {
                         // mapping country code to country iso2 is hard without a library helper or reverse lookup
                         // intl-tel-input doesn't easily accept '+33' to set country, it expects 'fr'
                         // simplifying: let default 'fr' or auto-detect work, or user selects.
                         // But if user has saved code, strictly we should show it.
                         // For now, let's rely on user selecting correct one or default.
                    }

                    input.addEventListener('countrychange', () => {
                         const countryData = iti.getSelectedCountryData();
                         @this.set('country_code', '+' + countryData.dialCode);
                    });
                    
                    // Sync phone number on input. 
                    // Note: input.value might not contain dial code with separateDialCode:true
                    input.addEventListener('input', () => {
                         // Strip non-digits for cleaner backend handling, though backend also sanitizes
                        @this.set('phone_number', input.value.replace(/\D/g, ''));
                    });
                }
            }" wire:ignore>

                <input x-ref="phoneInput" type="tel"
                    class="block w-full py-3 px-4 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 text-[15px] leading-6 text-gray-900"
                    placeholder="{{ __('Numéro de téléphone') }}">

            </div>
            @error('phone_number') <div class="text-sm text-left text-red-500">{{ $message }}</div> @enderror

            <div class="mt-8">
                <button type="submit"
                    class="w-full px-4 py-3 text-[15px] font-bold text-white transition-colors bg-[#007782] rounded-md hover:bg-[#00666f]">
                    {{ __('Envoyer') }}
                </button>
            </div>
        </form>

        @push('styles')
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
            <style>
                .iti {
                    width: 100%;
                }

                .iti__flag {
                    background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/img/flags.png");
                }

                @media (-webkit-min-device-pixel-ratio: 2),
                (min-resolution: 192dpi) {
                    .iti__flag {
                        background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/img/flags@2x.png");
                    }
                }
            </style>
        @endpush

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
        @endpush

        <div class="mt-6 text-[14px]">
            <span class="text-gray-600">{{ __('Un problème ?') }}</span>
            <a href="#" class="font-medium text-[#007782] hover:underline">
                {{ __('Obtenir de l\'aide') }}
            </a>
        </div>
    </div>
</div>
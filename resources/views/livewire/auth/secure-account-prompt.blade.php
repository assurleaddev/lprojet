<div class="w-full max-w-lg px-4 mx-auto text-center sm:px-0">
    <!-- Illustration -->
    <div class="flex justify-center mb-8">
        <!-- Placeholder for the illustration (Person with Phone/Shield) -->
        <svg class="w-32 h-32 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v6m-4-6h8"></path>
        </svg>
        <!-- You can replace the SVG above with the actual image when available: -->
        <!-- <img src="{{ asset('images/auth/secure-account.png') }}" alt="Secure Account" class="h-48" /> -->
    </div>

    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
        {{ __('Ajoute un numéro de téléphone pour sécuriser ton compte') }}
    </h2>

    <div class="text-[15px] leading-relaxed text-gray-600 dark:text-gray-400 space-y-4 text-left">
        <p>
            {{ __('Protège ton compte et tes revenus en ajoutant un numéro de téléphone.') }}
        </p>
        <p>
            {{ __('En ajoutant un numéro de téléphone, tu nous aides à :') }}
        </p>
        <ul class="list-disc list-inside pl-1 space-y-1">
            <li>{{ __('empêcher tout accès non autorisé') }}</li>
            <li>{{ __('protéger tes revenus si nous détectons une activité suspecte') }}</li>
        </ul>
    </div>

    <div class="mt-8">
        <a href="{{ route('auth.verify_phone') }}"
            class="block w-full px-4 py-3 text-[15px] font-bold text-white transition-colors bg-[#007782] rounded-md hover:bg-[#00666f] text-center">
            {{ __('Ajouter mon numéro de téléphone') }}
        </a>
    </div>
</div>
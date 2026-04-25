<footer class="bg-white border-t border-gray-200 mt-12">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

            {{-- Brand column --}}
            <div class="md:col-span-1">
                <a href="{{ route('home') }}" class="inline-block mb-6">
                    <img src="{{ config('settings.site_logo_lite') ?? asset('images/logo/lara-dashboard.png') }}" alt="{{ config('app.name') }}" class="h-12">
                </a>
                <p class="text-gray-500 text-sm leading-relaxed max-w-xs">
                    La seconde-main, c'est l'avenir et {{ config('app.name') }} te le prouve. Sur {{ config('app.name') }},
                    tu vends les vêtements qui ont encore des choses à vivre et
                    tu déniches des merveilles que tu ne trouves pas en boutique.
                </p>
            </div>

            {{-- À Propos column --}}
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-6">À Propos</h3>
                <ul class="space-y-4">
                    <li><a href="{{ route('faq') }}" class="text-gray-400 hover:text-gray-700 text-sm transition-colors">FAQ</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-gray-700 text-sm transition-colors">Contact</a></li>
                    <li><a href="{{ route('mentions-legales') }}" class="text-gray-400 hover:text-gray-700 text-sm transition-colors">Mentions Légales</a></li>
                    <li><a href="{{ route('cgu') }}" class="text-gray-400 hover:text-gray-700 text-sm transition-colors">CGU</a></li>
                </ul>
            </div>

            {{-- Suivez-nous sur column --}}
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-6">Suivez-nous sur</h3>

                {{-- Social icons --}}
                <div class="flex gap-3 mb-8">
                    <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-80" style="background-color: var(--brand)">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-80" style="background-color: var(--brand)">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-80" style="background-color: var(--brand)">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-80" style="background-color: var(--brand)">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.09-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                        </svg>
                    </a>
                </div>

                {{-- Help links --}}
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-400 hover:text-gray-700 text-sm transition-colors">Besoin d'aide?</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-gray-700 text-sm transition-colors">Comment vendre ?</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-gray-700 text-sm transition-colors">Comment acheter</a></li>
                </ul>
            </div>

        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-gray-200">
        <p class="text-center text-xs font-bold uppercase tracking-widest text-gray-400 py-5">
            USEDIGITAL &ndash; &copy; {{ date('Y') }}. ALL RIGHTS RESERVED.
        </p>
    </div>
</footer>

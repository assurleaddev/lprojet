<footer style="border-color:var(--line)">
  <div class="shell px-4 md:px-6 py-10">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
      <div class="col-span-2 md:col-span-1">
        <h3 class="font-bold text-lg mb-4">{{ config('app.name') }}</h3>
        <ul class="space-y-2">
          <li><a href="#" class="footer-link">About {{ config('app.name') }}</a></li>
          <li><a href="#" class="footer-link">Jobs</a></li>
          <li><a href="#" class="footer-link">Sustainability</a></li>
          <li><a href="#" class="footer-link">Press</a></li>
          <li><a href="#" class="footer-link">Advertising</a></li>
        </ul>
      </div>
      <div>
        <h3 class="font-bold text-lg mb-4">Discover</h3>
        <ul class="space-y-2">
          <li><a href="#" class="footer-link">How it works</a></li>
          <li><a href="#" class="footer-link">Mobile apps</a></li>
          <li><a href="#" class="footer-link">Help Centre</a></li>
          <li><a href="#" class="footer-link">Infoboard</a></li>
        </ul>
      </div>
      <div>
        <h3 class="font-bold text-lg mb-4">Help</h3>
        <ul class="space-y-2">
          <li><a href="#" class="footer-link">Help Centre</a></li>
          <li><a href="#" class="footer-link">Selling</a></li>
          <li><a href="#" class="footer-link">Buying</a></li>
          <li><a href="#" class="footer-link">Trust & Safety</a></li>
        </ul>
      </div>
      <div class="col-span-2 md:col-span-2">
        <h3 class="font-bold text-lg mb-4">Community</h3>
        <ul class="space-y-2">
          <li><a href="#" class="footer-link">Forum</a></li>
        </ul>
      </div>
    </div>

    <div class="mt-10 pt-8 border-t" style="border-color:var(--line)">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex gap-4">
          <a href="#"><svg class="h-6 w-6 text-gray-500 hover:text-black" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                clip-rule="evenodd" />
            </svg></a>
          <a href="#"><svg class="h-6 w-6 text-gray-500 hover:text-black" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.024.06 1.378.06 3.808s-.012 2.784-.06 3.808c-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.024.048-1.378.06-3.808.06s-2.784-.013-3.808-.06c-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.048-1.024-.06-1.378-.06-3.808s.012-2.784.60-3.808" />
            </svg></a>
          <a href="#"><svg class="h-6 w-6 text-gray-500 hover:text-black" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.09-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
            </svg></a>
        </div>
        <div class="flex gap-4">
          <img src="https://placehold.co/120x40/000000/ffffff?text=App+Store" alt="App Store" class="h-10" />
          <img src="https://placehold.co/120x40/000000/ffffff?text=Google+Play" alt="Google Play" class="h-10" />
        </div>
      </div>
      <p class="text-center text-xs text-gray-500 mt-8">&copy; {{ date('Y') }} {{ config('app.name') }} | <a href="#"
          class="hover:underline">Privacy
          Policy</a> | <a href="#" class="hover:underline">Terms &amp; Conditions</a></p>
    </div>
  </div>
</footer>
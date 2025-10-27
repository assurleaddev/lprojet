 <tbody>
     @forelse($categories as $level1Category)
         {{-- Level 1 Category Row --}}
         <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700" x-data="{ open: false }">
             <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                 <div class="flex items-center">
                     @if ($level1Category->children->isNotEmpty())
                         <button @click="open = !open"
                             class="mr-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                             <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': open }" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                 </path>
                             </svg>
                         </button>
                     @else
                         <span class="w-6 mr-2"></span> {{-- Placeholder for alignment --}}
                     @endif
                     <span>{{ $level1Category->name }}</span>
                 </div>
             </td>
             <td class="px-6 py-4">{{ $level1Category->slug }}</td>
             <td class="px-6 py-4 text-right">
                 @include('backend.marketplace.categories._actions', ['category' => $level1Category])
             </td>
         </tr>

         {{-- Level 2 Categories (Hidden by default) --}}
         @if ($level1Category->children->isNotEmpty())
             <tr x-show="open" x-cloak class="bg-gray-50 dark:bg-gray-900/50">
                 <td colspan="3" class="p-0">
                     <table class="w-full">
                         @foreach ($level1Category->children as $level2Category)
                             <tbody x-data="{ open: false }">
                                 <tr class="border-b dark:border-gray-700">
                                     <td class="px-6 py-3 w-1/2">
                                         <div class="flex items-center pl-8">
                                             @if ($level2Category->children->isNotEmpty())
                                                 <button @click="open = !open"
                                                     class="mr-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                                     <svg class="w-4 h-4 transition-transform"
                                                         :class="{ 'rotate-90': open }" fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24">
                                                         <path stroke-linecap="round" stroke-linejoin="round"
                                                             stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                     </svg>
                                                 </button>
                                             @else
                                                 <span class="w-6 mr-2"></span>
                                             @endif
                                             <span>{{ $level2Category->name }}</span>
                                         </div>
                                     </td>
                                     <td class="px-6 py-3">{{ $level2Category->slug }}</td>
                                     <td class="px-6 py-3 text-right">
                                         @include('backend.marketplace.categories._actions', [
                                             'category' => $level2Category,
                                         ])
                                     </td>
                                 </tr>
                                 {{-- Level 3 Categories (Hidden by default) --}}
                                 @if ($level2Category->children->isNotEmpty())
                                     <tr x-show="open" x-cloak>
                                         <td colspan="3" class="p-0">
                                             <table class="w-full">
                                                 @foreach ($level2Category->children as $level3Category)
                                                     <tr
                                                         class="border-b dark:border-gray-700 bg-gray-100 dark:bg-gray-800/50">
                                                         <td class="px-6 py-2 w-1/2">
                                                             <div class="flex items-center pl-16">
                                                                 <span class="w-6 mr-2"></span> {{-- No expand button for L3 --}}
                                                                 <span>{{ $level3Category->name }}</span>
                                                             </div>
                                                         </td>
                                                         <td class="px-6 py-2">{{ $level3Category->slug }}</td>
                                                         <td class="px-6 py-2 text-right">
                                                             @include(
                                                                 'backend.marketplace.categories._actions',
                                                                 ['category' => $level3Category]
                                                             )
                                                         </td>
                                                     </tr>
                                                 @endforeach
                                             </table>
                                         </td>
                                     </tr>
                                 @endif
                             </tbody>
                         @endforeach
                     </table>
                 </td>
             </tr>
         @endif
     @empty
         <tr>
             <td colspan="3" class="px-6 py-4 text-center">No categories found.</td>
         </tr>
     @endforelse
 </tbody>

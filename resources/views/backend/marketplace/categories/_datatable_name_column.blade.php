<div x-data="{ open: false }">
    {{-- Level 1 --}}
    <div class="flex items-center">
        @if($level1Category->children->isNotEmpty())
            <button @click="open = !open" class="mr-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        @else
            <span class="w-4 h-4 mr-2"></span>
        @endif
        <span class="font-medium text-gray-900 dark:text-white">{{ $level1Category->name }}</span>
    </div>

    {{-- Level 2 & 3 --}}
    <div x-show="open" x-cloak class="mt-2 space-y-2">
        @foreach($level1Category->children as $level2Category)
            <div x-data="{ open_l2: false }">
                <div class="flex items-center pl-8">
                    @if($level2Category->children->isNotEmpty())
                        <button @click="open_l2 = !open_l2" class="mr-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': open_l2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @else
                        <span class="w-4 h-4 mr-2"></span>
                    @endif
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $level2Category->name }}</span>
                </div>
                <div x-show="open_l2" x-cloak class="mt-2 space-y-2">
                     @foreach($level2Category->children as $level3Category)
                         <div class="flex items-center pl-16">
                           <span class="w-4 h-4 mr-2"></span>
                           <span class="font-medium text-gray-700 dark:text-gray-300">{{ $level3Category->name }}</span>
                        </div>
                     @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
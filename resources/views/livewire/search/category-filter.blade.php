<div class="relative" x-data="{ open: false }">
    <!-- Trigger Button -->
    <button @click="open = !open"
        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 transition-colors"
        :class="{ 'ring-1': {{ !empty($categoryIds) ? 'true' : 'false' }} }"
        :style="{{ !empty($categoryIds) ? 'true' : 'false' }} ? 'border-color: var(--brand); --tw-ring-color: var(--brand); color: var(--brand)' : ''">
        <span>Category</span>
        @if(!empty($categoryIds))
            <span class="flex items-center justify-center text-white text-xs w-5 h-5 rounded-full"
                style="background-color: var(--brand)">{{ count($categoryIds) }}</span>
        @endif
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute z-50 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
        style="display: none;">

        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center gap-2">
                @if($currentViewCategory)
                    <button wire:click="goBack" class="p-1 hover:bg-gray-200 rounded-full transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                    <span class="font-semibold text-gray-900 line-clamp-1">{{ $title }}</span>
                @else
                    <span class="font-semibold text-gray-900">Categories</span>
                @endif
            </div>
            <button @click="open = false" class="text-xs text-gray-500 hover:text-gray-900 font-medium">Close</button>
        </div>

        <!-- List -->
        <div class="max-h-[400px] overflow-y-auto">
            @if($currentViewCategory)
                <!-- "All" option for current category -->
                <a href="{{ route('search', array_merge(request()->except(['categories', 'page']), ['categories' => [$currentViewCategory->id]])) }}"
                    class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-50 group">
                    <span class="text-gray-900 font-medium">All in {{ $currentViewCategory->name }}</span>
                    <div class="w-5 h-5 rounded-full border border-gray-300 {{ in_array($currentViewCategory->id, $categoryIds) ? 'border-[var(--brand)]' : '' }}"
                        style="{{ in_array($currentViewCategory->id, $categoryIds) ? 'background-color: var(--brand)' : '' }}">
                    </div>
                </a>
            @else
                <!-- Clear filter option if root -->
                <a href="{{ route('search', request()->except(['categories', 'page'])) }}"
                    class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-50 group text-gray-500">
                    <span class="font-medium">All Categories</span>
                </a>
            @endif

            @foreach($categories as $category)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-50 group cursor-pointer"
                    @if($category->children->count() > 0) wire:click="drillDown({{ $category->id }})" @else
                        onclick="window.location='{{ route('search', array_merge(request()->except(['categories', 'page']), ['categories' => [$category->id]])) }}'"
                    @endif>
                    <span class="text-gray-700 {{ in_array($category->id, $categoryIds) ? 'font-bold' : '' }}"
                        style="{{ in_array($category->id, $categoryIds) ? 'color: var(--brand)' : '' }}">
                        {{ $category->name }}
                    </span>

                    @if($category->children->count() > 0)
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    @else
                        <!-- Selection Radio -->
                        <div class="w-5 h-5 rounded-full border border-gray-300 {{ in_array($category->id, $categoryIds) ? 'border-[var(--brand)]' : '' }}"
                            style="{{ in_array($category->id, $categoryIds) ? 'background-color: var(--brand)' : '' }}">
                        </div>
                    @endif
                </div>
            @endforeach

            @if($categories->isEmpty())
                <div class="p-8 text-center text-gray-500 italic text-sm">
                    No sub-categories found.
                </div>
            @endif
        </div>
    </div>
</div>
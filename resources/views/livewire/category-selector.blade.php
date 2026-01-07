<div class="relative w-full" x-data="{ open: false }">
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">

    <!-- Trigger Button -->
    <button type="button" @click="open = !open"
        class="flex items-center justify-between w-full px-4 py-2.5 bg-white border rounded-md text-left transition-colors focus:ring-1 focus:ring-teal-500 {{ $value ? 'border-teal-500 ring-1 ring-teal-500' : 'border-gray-300' }}">
        <span class="block truncate {{ $value ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
            {{ $selectedLabel }}
        </span>
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" @click.outside="open = false" x-cloak x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-80 overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between p-3 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                @if($currentViewCategory)
                    <button wire:click="goBack" type="button" class="p-1 hover:bg-gray-200 rounded-full transition-colors">
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
            <button @click="open = false" type="button"
                class="text-xs text-gray-500 hover:text-gray-900 font-medium">Close</button>
        </div>

        <!-- List -->
        <div class="overflow-y-auto flex-1">
            @if($categories->isEmpty())
                <div class="p-4 text-center text-gray-500 italic text-sm">
                    No sub-categories found.
                </div>
                {{-- Allow selecting the current parent if it's a leaf/potential selection --}}
                @if($currentViewCategory)
                    <div wire:click="select({{ $currentViewCategory->id }})" @click="open = false"
                        class="flex items-center justify-between px-4 py-3 hover:bg-teal-50 border-b border-gray-50 cursor-pointer group">
                        <span class="text-teal-700 font-bold">Select "{{ $currentViewCategory->name }}"</span>
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @endif
            @else
                @foreach($categories as $category)
                    <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-50 cursor-pointer group"
                        wire:click="{{ $category->children->count() > 0 ? 'drillDown(' . $category->id . ')' : 'select(' . $category->id . ')' }}"
                        @if($category->children->count() == 0) @click="open = false" @endif>
                        <span class="text-gray-700 {{ $value == $category->id ? 'font-bold text-teal-700' : '' }}">
                            {{ $category->name }}
                        </span>

                        @if($category->children->count() > 0)
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        @else
                            @if($value == $category->id)
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
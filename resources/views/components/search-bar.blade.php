<div x-data="{
    query: '{{ request('query') }}',
    type: '{{ request('type', 'product') }}',
    suggestions: [],
    isOpen: false,
    loading: false,
    
    fetchSuggestions() {
        if (this.query.length < 2) {
            this.suggestions = [];
            this.isOpen = false;
            return;
        }

        this.loading = true;
        fetch(`{{ route('search.suggestions') }}?query=${this.query}&type=${this.type}`)
            .then(res => res.json())
            .then(data => {
                this.suggestions = data;
                this.isOpen = this.suggestions.length > 0;
            })
            .finally(() => {
                this.loading = false;
            });
    },
    
    selectSuggestion(url) {
        window.location.href = url;
    }
}" @click.away="isOpen = false" class="w-full relative">

    <form x-ref="searchForm" action="{{ route('search') }}" method="GET"
        class="w-full flex items-center {{ $attributes->get('class') }}">
        <div <div class="relative z-[60]" x-data="{ openType: false }">
            <button type="button" @click="openType = !openType" @click.away="openType = false"
                class="flex items-center gap-2 bg-gray-50 border-r border-gray-200 rounded-l-lg hover:bg-gray-100 transition-colors py-2.5 pl-4 pr-3 cursor-pointer h-full">
                <span class="text-sm font-medium text-gray-700"
                    x-text="type === 'product' ? 'Items' : 'Members'"></span>
                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': openType}"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <!-- Custom Dropdown Options -->
            <div x-show="openType" x-transition.opacity.duration.200ms
                class="absolute top-full left-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 py-1 overflow-hidden"
                style="display: none;">
                <button type="button" @click="type = 'product'; openType = false; fetchSuggestions()"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-teal-600 flex items-center gap-2">
                    <span>Items</span>
                    <span x-show="type === 'product'" class="text-teal-600 ml-auto">&check;</span>
                </button>
                <button type="button" @click="type = 'user'; openType = false; fetchSuggestions()"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-teal-600 flex items-center gap-2">
                    <span>Members</span>
                    <span x-show="type === 'user'" class="text-teal-600 ml-auto">&check;</span>
                </button>
            </div>

            <!-- Hidden Input to carry value -->
            <input type="hidden" name="type" x-model="type">
        </div>

        <div class="flex-1 relative">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 10-14 0 7 7 0 0014 0z">
                </path>
            </svg>
            <input type="text" name="query" x-model="query" @input.debounce.300ms="fetchSuggestions()"
                class="search-input pl-10 w-full focus:outline-none" placeholder="Search for items or members"
                autocomplete="off">

            <!-- Loading Indicator -->
            <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
        </div>
        <button type="submit" class="hidden"></button>
    </form>

    <!-- Dropdown Results -->
    <div x-show="isOpen" x-transition.opacity.duration.200ms
        class="absolute top-full left-0 w-full bg-white border border-gray-100 rounded-b-lg shadow-lg z-50 overflow-hidden mt-1">
        <ul>
            <template x-for="item in suggestions" :key="item.url">
                <li>
                    <a :href="item.url"
                        class="flex items-center px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition-colors">
                        <!-- Image/Icon -->
                        <div class="flex-shrink-0 mr-3">
                            <template x-if="item.image">
                                <img :src="item.image" class="w-10 h-10 rounded-md object-cover">
                            </template>
                            <template x-if="!item.image && item.type === 'category'">
                                <div
                                    class="w-10 h-10 rounded-md bg-gray-100 flex items-center justify-center text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                        </path>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="!item.image && item.type !== 'category'">
                                <div
                                    class="w-10 h-10 rounded-md bg-gray-100 flex items-center justify-center text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        <!-- Text -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="item.label"></p>
                            <p class="text-xs text-gray-500 truncate" x-text="item.sub"></p>
                        </div>

                        <!-- Arrow -->
                        <div class="text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </div>
                    </a>
                </li>
            </template>
        </ul>
        <div class="bg-gray-50 px-4 py-2 text-center border-t border-gray-100">
            <button @click="$refs.searchForm.submit()" class="text-xs text-teal-600 font-semibold hover:underline">
                View all results for "<span x-text="query"></span>"
            </button>
        </div>
    </div>
</div>
<div x-data="{
    activeMenu: null,
    activeSubCategory: null,
    hideTimer: null,
    show(menu, subId = null) {
        clearTimeout(this.hideTimer);
        this.activeMenu = menu;
        this.activeSubCategory = subId; // Auto-focus the first child
    },
    scheduleHide() {
        this.hideTimer = setTimeout(() => {
            this.activeMenu = null;
        }, 150);
    },
    cancelHide() {
        clearTimeout(this.hideTimer);
    },
    activateSubCategory(id) {
        this.activeSubCategory = id;
    }
}" @mouseleave="scheduleHide()" class="w-full relative">
    
    <!-- Bottom nav -->
    <nav class="hidden md:flex items-center justify-center gap-6 relative px-4 md:px-6">
        @foreach($categories as $category)
            <a href="{{ route('search', ['categories' => [$category->id]]) }}" 
               class="nav-link" 
               :class="{ 'active': activeMenu === {{ $category->id }} }" 
               @mouseenter="show({{ $category->id }}, {{ $category->children->first()?->id ?? 'null' }})">
                {{ $category->name }}
            </a>
        @endforeach
        <a href="#" class="nav-link">About</a>
        <a href="#" class="nav-link">Our Platform</a>
    </nav>

    <!-- Megamenus -->
    <div id="megamenus-container" class="absolute left-0 w-full z-50" style="top: 100%;" @mouseenter="cancelHide()" @mouseleave="scheduleHide()">
        
        @foreach($categories as $category)
            <div x-show="activeMenu === {{ $category->id }}" 
                 class="absolute top-0 left-0 w-full bg-white border-t border-gray-100 shadow-xl z-50"
                 x-transition
                 style="display: none;">
                
                <div class="shell px-4 md:px-6 py-8 flex gap-10 min-h-[300px]">
                    <!-- Left list (Subcategories) -->
                    <div class="w-1/4 border-r border-gray-100 pr-4">
                        <div class="mb-4">
                            <a href="{{ route('search', ['categories' => [$category->id]]) }}" class="flex items-center gap-2 text-teal-600 font-bold hover:underline mb-4">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                See all
                            </a>
                        </div>
                        
                        @if($category->children->count() > 0)
                            <ul class="space-y-1">
                                @foreach($category->children as $child)
                                    <li @mouseenter="activateSubCategory({{ $child->id }})">
                                        <a href="{{ route('search', ['categories' => [$child->id]]) }}" 
                                           class="megamenu-category-link justify-between group"
                                           :class="{ 'active': activeSubCategory === {{ $child->id }} }">
                                            <div class="flex items-center gap-3">
                                                @if($child->icon)
                                                    <iconify-icon icon="{{ $child->icon }}" class="text-xl text-gray-400 group-hover:text-teal-600 transition-colors"></iconify-icon>
                                                @else
                                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-teal-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                                @endif
                                                <span>{{ $child->name }}</span>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-300 group-hover:text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-400 italic">No sub-categories found.</p>
                        @endif
                    </div>
                    
                    <!-- Right columns (Sub-subcategories) -->
                    <div class="w-3/4 pl-4">
                        <!-- Default view (if needed, or placeholder) -->
                        <div x-show="!activeSubCategory" class="text-gray-400 text-sm italic">
                            Select a category to see more options.
                        </div>

                        <!-- Loop for sub-category contents -->
                        @foreach($category->children as $child)
                            <div x-show="activeSubCategory === {{ $child->id }}" style="display: none;">
                                <div class="mb-4">
                                     <a href="{{ route('search', ['categories' => [$child->id]]) }}" class="font-bold text-gray-800 hover:text-teal-600 mb-2 inline-block">See all {{ $child->name }}</a>
                                </div>
                                
                                @if($child->children->count() > 0)
                                    <div class="grid grid-cols-3 gap-x-8 gap-y-4">
                                        @foreach($child->children as $grandChild)
                                            <a href="{{ route('search', ['categories' => [$grandChild->id]]) }}" class="megamenu-link">{{ $grandChild->name }}</a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-400 text-sm">No sub-categories found.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
        
    </div>
</div>
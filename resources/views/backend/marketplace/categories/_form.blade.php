<div class="p-6">
    <div class="mb-4">
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
            required>
        @error('name')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
    </div>

    <div class="mb-4">
        <label for="parent_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Parent
            Category</label>
        <select id="parent_id" name="parent_id"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
            <option value="">None</option>
            @foreach($categories as $parent)
                {{-- Prevent a category from being its own parent --}}
                @if(!isset($category) || $category->id !== $parent->id)
                    <option value="{{ $parent->id }}" @isset($category) @selected($category->parent_id == $parent->id) @endisset>
                        {{ $parent->name }}
                    </option>
                    {{-- Now, loop through the children of the L1 category --}}
                    @if($parent->children)
                        @foreach($parent->children as $child)
                            {{-- Prevent a category from being its own parent --}}
                            @if(!isset($category) || $category->id !== $child->id)
                                <option value="{{ $child->id }}" @isset($category) @selected($category->parent_id == $child->id) @endisset>
                                    &nbsp;&nbsp;&nbsp;{{ $child->name }}
                                </option>
                            @endif
                        @endforeach
                    @endif
                @endif
            @endforeach
        </select>
        @error('parent_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
    </div>
</div>

<div class="mb-4"
    x-data="{ iconType: '{{ $category->getFirstMediaUrl('icon') ? 'image' : ($category->icon ? 'class' : 'class') }}' }">
    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category Icon</label>

    <div class="flex space-x-4 mb-3">
        <label class="inline-flex items-center">
            <input type="radio" x-model="iconType" value="class" class="form-radio text-blue-600">
            <span class="ml-2">Icon Class</span>
        </label>
        <label class="inline-flex items-center">
            <input type="radio" x-model="iconType" value="image" class="form-radio text-blue-600">
            <span class="ml-2">Upload Image</span>
        </label>
    </div>

    <!-- Icon Class Input (Picker) -->
    <div x-show="iconType === 'class'" x-data="{
        search: '',
        open: false,
        selectedIcon: '{{ old('icon', $category->icon ?? '') }}',
        icons: [
            'lucide:shopping-bag', 'lucide:shirt', 'lucide:watch', 'lucide:smartphone', 'lucide:laptop',
            'lucide:book', 'lucide:car', 'lucide:music', 'lucide:gift', 'lucide:zap',
            'lucide:percent', 'lucide:star', 'lucide:heart', 'lucide:camera', 'lucide:headphones',
            'lucide:monitor', 'lucide:sofa', 'lucide:coffee', 'lucide:utensils', 'lucide:wrench',
            'lucide:hammer', 'lucide:briefcase', 'lucide:baby', 'lucide:dog', 'lucide:cross',
            'lucide:plane', 'lucide:home', 'lucide:search', 'lucide:menu', 'lucide:user',
            'lucide:settings', 'lucide:bell', 'lucide:calendar', 'lucide:map-pin', 'lucide:info',
            'lucide:check', 'lucide:x', 'lucide:plus', 'lucide:minus', 'lucide:trash',
            'lucide:edit', 'lucide:eye', 'lucide:download', 'lucide:upload', 'lucide:filter',
            'lucide:sort', 'lucide:grid', 'lucide:list', 'lucide:folder', 'lucide:file',
            'lucide:image', 'lucide:video', 'lucide:smile', 'lucide:frown'
        ],
        get filteredIcons() {
            if (this.search === '') return this.icons.slice(0, 30);
            return this.icons.filter(icon => icon.toLowerCase().includes(this.search.toLowerCase()));
        },
        selectIcon(icon) {
            this.selectedIcon = icon;
            this.open = false;
        }
    }" @click.outside="open = false" class="relative mb-4">
        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Select Icon</label>
        
        <!-- Search Icons -->
        <input type="text" x-model="search" @focus="open = true" placeholder="Search icons..." 
            class="mb-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

        <!-- Hidden Input for Form Submission -->
        <input type="hidden" name="icon" :value="selectedIcon">

        <!-- Selected Icon Preview (When Closed) -->
         <div x-show="!open && selectedIcon" class="mb-3 flex items-center p-2 border border-gray-200 rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <span class="text-sm text-gray-500 mr-2">Selected:</span>
            <iconify-icon :icon="selectedIcon" class="text-2xl text-blue-600"></iconify-icon>
            <span x-text="selectedIcon" class="ml-2 font-mono text-sm"></span>
            <button type="button" @click="selectedIcon = ''" class="ml-auto text-gray-400 hover:text-red-500">
                <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
            </button>
        </div>

        <!-- Icon Grid Dropdown -->
        <div x-show="open" x-transition 
            class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700 max-h-60 overflow-y-auto p-2"
            style="top: 100%;">
            <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2">
                <template x-for="icon in filteredIcons" :key="icon">
                    <div @click="selectIcon(icon)" 
                        :class="{ 'bg-blue-100 ring-2 ring-blue-500 dark:bg-blue-900': selectedIcon === icon, 'hover:bg-gray-100 dark:hover:bg-gray-700': selectedIcon !== icon }"
                        class="cursor-pointer p-2 rounded flex items-center justify-center transition-all"
                        :title="icon">
                        <iconify-icon :icon="icon" class="text-2xl text-gray-700 dark:text-gray-200"></iconify-icon>
                    </div>
                </template>
            </div>
            <div x-show="filteredIcons.length === 0" class="p-2 text-center text-gray-500 text-sm">
                No icons found.
            </div>
        </div>
        <p class="mt-1 text-sm text-gray-500" x-show="!open">Click to search and see all icons.</p>
    </div>

    <!-- Image Upload Input -->
    <div x-show="iconType === 'image'">
        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Upload Icon Image</label>
        <input type="file" name="icon_image" accept="image/*"
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
        @if(isset($category) && $category->getFirstMediaUrl('icon'))
            <div class="mt-2">
                <p class="text-sm text-gray-500 mb-1">Current Image:</p>
                <img src="{{ $category->getFirstMediaUrl('icon') }}" alt="Category Icon"
                    class="h-12 w-12 object-cover rounded">
            </div>
        @endif
    </div>
</div>

<div class="mb-4">
    <label class="form-label">Assign Attributes</label>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
        @foreach($attributes as $attribute)
            <label class="flex items-center space-x-3">
                <input type="checkbox" name="attributes[]" value="{{ $attribute->id }}" @if(isset($category) && $category->attributes->contains($attribute->id)) checked @endif
                    class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-blue-600">
                <span class="text-gray-700 dark:text-gray-300">{{ $attribute->name }}</span>
            </label>
        @endforeach
    </div>
    <p class="text-sm text-gray-500 mt-1">Select all attributes that apply to this category.</p>
</div>

<div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <div class="flex justify-end">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mr-3">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Category</button>
    </div>
</div>
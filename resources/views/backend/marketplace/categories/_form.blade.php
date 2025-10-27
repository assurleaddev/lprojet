<div class="p-6">
    <div class="mb-4">
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
        @error('name')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
    </div>

    <div class="mb-4">
        <label for="parent_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Parent Category</label>
        <select id="parent_id" name="parent_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
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
<div class="mb-4">
    <label class="form-label">Assign Attributes</label>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
        @foreach($attributes as $attribute)
            <label class="flex items-center space-x-3">
                <input type="checkbox" name="attributes[]" value="{{ $attribute->id }}"
                    @if(isset($category) && $category->attributes->contains($attribute->id)) checked @endif
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
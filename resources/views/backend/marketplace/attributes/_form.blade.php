<div class="p-6">
    <div class="mb-4">
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Attribute Name</label>
        <input type="text" id="name" name="name" placeholder="e.g. Size" value="{{ old('name', $attribute->name ?? '') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
        @error('name')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
    </div>

    <div class="mb-4">
        <label for="options" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Options</label>
        <textarea id="options" name="options" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Enter options separated by a comma. e.g. S, M, L, XL" required>{{ old('options', isset($attribute) ? $attribute->options->pluck('value')->implode(', ') : '') }}</textarea>
        @error('options')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
    </div>
</div>

<div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <div class="flex justify-end">
        <a href="{{ route('admin.marketplace.attributes.index') }}" class="btn btn-secondary mr-3">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Attribute</button>
    </div>
</div>
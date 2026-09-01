<div>
    <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">{{ __('Brand name') }}</label>
    <input type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required autofocus
        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 dark:bg-gray-800 dark:text-white">
    @error('name')
        <span class="text-xs text-red-500">{{ $message }}</span>
    @enderror
    <p class="mt-1 text-xs text-gray-500">{{ __('The slug is generated automatically from the name.') }}</p>
</div>

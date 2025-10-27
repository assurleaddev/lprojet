<x-card>
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="mb-6">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" class="form-input" required>
            @error('name')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="description" class="form-label">Description</label>
            <x-quill-editor
                name="description"
                id="description"
                value="{!! old('description', $product->description ?? '') !!}"
                placeholder="Enter product description...">
            </x-quill-editor>
            @error('description')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
        </div>
    </div>
</x-card>
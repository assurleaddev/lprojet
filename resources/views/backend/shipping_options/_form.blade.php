<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Left Column -->
    <div class="space-y-6">
        <!-- Label -->
        <div>
            <label for="label" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('Label') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="label" id="label"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                placeholder="e.g. Royal Mail 24" value="{{ old('label', $shippingOption->label ?? '') }}" required>
            @error('label')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Key -->
        <div>
            <label for="key" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('Key (Internal ID)') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="key" id="key"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                placeholder="e.g. shipping_royal_mail_24" value="{{ old('key', $shippingOption->key ?? '') }}" required>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Must be unique (e.g. shipping_royal_mail)</p>
            @error('key')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Type -->
        <div>
            <label for="type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('Type') }} <span class="text-red-500">*</span>
            </label>
            <select name="type" id="type"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                <option value="home_pickup" {{ old('type', $shippingOption->type ?? '') == 'home_pickup' ? 'selected' : '' }}>
                    {{ __('Home Pickup') }}
                </option>
                <option value="drop_off" {{ old('type', $shippingOption->type ?? '') == 'drop_off' ? 'selected' : '' }}>
                    {{ __('Drop Off (ParcelShop/Locker)') }}
                </option>
            </select>
            @error('type')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Icon Class -->
        <div>
            <label for="icon_class" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('Icon Class (Tailwind)') }}
            </label>
            <input type="text" name="icon_class" id="icon_class"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                placeholder="e.g. bg-red-600" value="{{ old('icon_class', $shippingOption->icon_class ?? '') }}">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Background color class usually.</p>
            @error('icon_class')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Logo Upload -->
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="logo_input">
                {{ __('Logo') }}
            </label>

            <div class="flex items-center justify-center w-full">
                <label for="logo_input"
                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        @if(isset($shippingOption) && $shippingOption->logo_path)
                            <img src="{{ asset('storage/' . $shippingOption->logo_path) }}" alt="Current Logo"
                                class="h-16 object-contain mb-2">
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">Click to replace</p>
                        @else
                            <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                            </svg>
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to
                                    upload</span> or drag and drop</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 1MB)</p>
                        @endif
                    </div>
                    <input id="logo_input" type="file" name="logo" class="hidden" accept="image/*" />
                </label>
            </div>
            @error('logo')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('Description') }}
            </label>
            <textarea name="description" id="description" rows="4"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                placeholder="Information shown to the user...">{{ old('description', $shippingOption->description ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports HTML (e.g. links)</p>
            @error('description')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Active Status -->
        <div class="flex items-center">
            <input id="is_active" name="is_active" type="checkbox" value="1"
                class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                {{ old('is_active', $shippingOption->is_active ?? true) ? 'checked' : '' }}>
            <label for="is_active" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                {{ __('Active') }}
            </label>
        </div>
    </div>
</div>

<div class="mt-6">
    <x-buttons.submit-buttons cancelUrl="{{ route('admin.shipping-options.index') }}" />
</div>

<script>
    // Simple preview script
    document.getElementById('logo_input').addEventListener('change', function (event ) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) { 
                // Find or create preview
                let container = event.target.closest('label').querySelector('div');
                container.innerHTML = `<img src="${e.target.result}" class="h-16 object-contain mb-2"><p class="mb-2 text-sm text-gray-500">Selected: ${file.name}</p>`;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
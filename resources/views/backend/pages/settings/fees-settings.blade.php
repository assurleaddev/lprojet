<div class="rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
            {{ __('Fees & Commission Settings') }}
        </h3>
    </div>
    <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Platform Commission -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Platform Commission (%)') }}
                </label>
                <div class="relative">
                    <input type="number" step="0.01" min="0" max="100" name="platform_commission_percentage"
                        value="{{ config('settings.platform_commission_percentage') ?? '10' }}" class="form-control"
                        placeholder="10">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">%</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __('Percentage taken from each successful sale.') }}</p>
            </div>

            <!-- Refund Commission -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Refund Commission (%)') }}
                </label>
                <div class="relative">
                    <input type="number" step="0.01" min="0" max="100" name="refund_commission_percentage"
                        value="{{ config('settings.refund_commission_percentage') ?? '0' }}" class="form-control"
                        placeholder="0">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">%</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __('Percentage kept by platform when refunding (optional).') }}
                </p>
            </div>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-4">{{ __('Buyer Fees') }}</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Delivery Fee -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Default Shipping Fee (Fallback)') }}
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" name="delivery_fee_fixed"
                            value="{{ config('settings.delivery_fee_fixed') ?? '5.00' }}" class="form-control"
                            placeholder="5.00">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">MAD</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Fixed shipping cost charged to buyer when no options available.') }}</p>
                </div>

                <!-- Default Shipping Options -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Default Shipping Options') }}
                    </label>
                    <div class="space-y-2 p-3 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900/50">
                        @php
                            $allShippingOptions = \App\Models\ShippingOption::where('is_active', true)->get();
                            $defaultOptions = json_decode(config('settings.default_shipping_options', '[]'), true) ?? [];
                        @endphp
                        @forelse($allShippingOptions as $option)
                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 p-2 rounded">
                                <input type="checkbox" name="default_shipping_options[]" value="{{ $option->id }}"
                                    {{ in_array($option->id, $defaultOptions) ? 'checked' : '' }}
                                    class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $option->label }} ({{ $option->type === 'home_pickup' ? 'Home' : 'Pickup Point' }}) - {{ number_format($option->price, 2) }} MAD
                                </span>
                            </label>
                        @empty
                            <p class="text-xs text-gray-500 italic">{{ __('No shipping options available. Create them first.') }}</p>
                        @endforelse
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('These options will be used when sellers haven\'t configured their own shipping preferences.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <!-- Buyer Protection Fixed -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Buyer Protection Fee (Fixed)') }}
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" name="buyer_protection_fee_fixed"
                            value="{{ config('settings.buyer_protection_fee_fixed') ?? '0.70' }}" class="form-control"
                            placeholder="0.70">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">MAD</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Fixed amount added to buyer protection fee.') }}</p>
                </div>

                <!-- Buyer Protection Percentage -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Buyer Protection Fee (%)') }}
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="buyer_protection_fee_percentage"
                            value="{{ config('settings.buyer_protection_fee_percentage') ?? '5' }}" class="form-control"
                            placeholder="5">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Percentage of item price added to buyer protection fee.') }}
                    </p>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 italic">
                {{ __('Total Buyer Protection Fee = (Item Price * Percentage) + Fixed Amount') }}
            </p>
        </div>

    </div>
</div>
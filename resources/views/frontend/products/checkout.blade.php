@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        @php
            $price = isset($checkoutPrice) ? $checkoutPrice : $product->price;
            // Calculations are done in controller or updated via JS
            // Initial shipping fee (defaults to first option or fixed)
            $initialShippingFee = $shippingOptions->count() > 0 ? $shippingOptions->first()->price : $deliveryFeeFixed;
            $initialTotal = $price + $protectionFee + $initialShippingFee;
            
            $walletBalance = auth()->user()->wallet->balance ?? 0;
            // Note: $canPayWithWallet needs to be checked dynamically via JS or assume initial check
            $canPayWithWallet = $walletBalance >= $initialTotal;
        @endphp

        <form action="{{ route('checkout.process') }}" method="POST"
            class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8" id="checkout-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            @if(isset($offer))
                <input type="hidden" name="offer_id" value="{{ $offer->id }}">
            @endif
            
            <!-- Hidden inputs for JS to read -->
            <input type="hidden" id="base_price" value="{{ $price }}">
            <input type="hidden" id="protection_fee" value="{{ $protectionFee }}">
            <input type="hidden" id="wallet_balance" value="{{ $walletBalance }}">

            <!-- Left Column (Content) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Product Summary -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex gap-4">
                        <img src="{{ $product->getFeaturedImageUrl('preview') }}" alt="{{ $product->name }}"
                            class="w-20 h-20 object-cover rounded-md border border-gray-100 bg-gray-50">
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900 line-clamp-1">{{ $product->name }}</h2>
                                    <p class="text-xs text-gray-500 mt-1 uppercase">
                                        {{ $product->brand ? $product->brand->name : '' }}</p>
                                </div>
                            </div>
                            <div class="mt-3 space-y-1">
                                <!-- Size, Condition, Color can be here -->
                                @if($product->size) <div class="text-sm"><span class="text-gray-500 text-xs uppercase font-semibold w-24 inline-block">Size</span> {{ $product->size }}</div> @endif
                                @if($product->condition) <div class="text-sm"><span class="text-gray-500 text-xs uppercase font-semibold w-24 inline-block">Condition</span> {{ ucwords(str_replace('_', ' ', $product->condition)) }}</div> @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Address Section -->
                <div>
                    <h3 class="font-bold text-gray-900 mb-2">Address</h3>
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden divide-y divide-gray-100">
                        @if(isset($addresses) && $addresses->count() > 0)
                            @foreach($addresses as $address)
                                <label class="flex items-start gap-3 p-4 cursor-pointer hover:bg-gray-50 transition-colors">
                                    <div class="mt-1">
                                        <input type="radio" name="address_id" value="{{ $address->id }}" 
                                               {{ $loop->first ? 'checked' : '' }}
                                               class="w-5 h-5 text-vinted-teal border-gray-300 focus:ring-vinted-teal">
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $address->full_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $address->address_line_1 }}</p>
                                        <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->postcode }}</p>
                                    </div>
                                </label>
                            @endforeach
                        @else
                            <div class="p-4 text-center text-gray-500 text-sm">No addresses found.</div>
                        @endif
                        <a href="{{ route('settings.postage') }}" class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors group">
                            <span class="text-gray-600 font-medium">Add a new address</span>
                            <span class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-gray-400 group-hover:border-vinted-teal group-hover:text-vinted-teal pb-0.5">+</span>
                        </a>
                    </div>
                </div>

                @if($shippingOptions->isNotEmpty())
                    <div>
                        <h3 class="font-bold text-gray-900 mb-2">Delivery option</h3>
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden" id="delivery-options-container">
                            @php
                                $homeOptions = $shippingOptions->where('type', 'home_pickup');
                                $pickupOptions = $shippingOptions->where('type', 'drop_off');
                            @endphp

                            <!-- Pickup Point Category -->
                            <div class="border-b border-gray-100 last:border-0">
                                <label class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors delivery-category-label">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="delivery_category" value="pickup"
                                            {{ $pickupOptions->isNotEmpty() ? ($pickupOptions->first()->id == ($initialShippingOption->id ?? 0) ? 'checked' : '') : 'disabled' }}
                                            class="w-5 h-5 text-vinted-teal border-gray-300 focus:ring-vinted-teal category-radio">
                                        <div>
                                            <span class="block font-medium text-gray-900 {{ $pickupOptions->isEmpty() ? 'text-gray-400' : '' }}">Ship to pick-up point</span>
                                            <span class="block text-xs text-gray-500 {{ $pickupOptions->isEmpty() ? 'text-gray-300' : '' }}">Mondial Relay, Chronopost, etc.</span>
                                        </div>
                                    </div>
                                    @if($pickupOptions->isNotEmpty())
                                        <span class="text-sm font-semibold text-gray-700">From {{ number_format($pickupOptions->min('price'), 2) }} MAD</span>
                                    @else
                                        <span class="text-xs text-gray-400">Not available</span>
                                    @endif
                                </label>
                                
                                <!-- Sub-options for Pickup -->
                                <div class="bg-gray-50 pl-12 pr-4 py-2 space-y-2 {{ $pickupOptions->isEmpty() ? 'hidden' : '' }} category-options" id="options-pickup" style="display: none;">
                                    @foreach($pickupOptions as $option)
                                        <label class="flex items-center justify-between py-2 cursor-pointer">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="shipping_option_id" value="{{ $option->id }}" 
                                                    data-price="{{ $option->price }}"
                                                    data-category="pickup"
                                                    class="w-4 h-4 text-vinted-teal border-gray-300 focus:ring-vinted-teal shipping-option-radio">
                                                <div class="flex items-center gap-2">
                                                    @if($option->icon_class)<div class="w-2 h-2 rounded-full {{ $option->icon_class }}"></div>@endif
                                                    <span class="text-sm text-gray-700">{{ $option->label }}</span>
                                                </div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($option->price, 2) }} MAD</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Home Delivery Category -->
                            <div class="border-b border-gray-100 last:border-0">
                                <label class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors delivery-category-label">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="delivery_category" value="home"
                                            {{ $homeOptions->isNotEmpty() ? ($homeOptions->first()->id == ($initialShippingOption->id ?? 0) ? 'checked' : '') : 'disabled' }}
                                            class="w-5 h-5 text-vinted-teal border-gray-300 focus:ring-vinted-teal category-radio">
                                        <div>
                                            <span class="block font-medium text-gray-900 {{ $homeOptions->isEmpty() ? 'text-gray-400' : '' }}">Ship to home</span>
                                            <span class="block text-xs text-gray-500 {{ $homeOptions->isEmpty() ? 'text-gray-300' : '' }}">La Poste / Amana</span>
                                        </div>
                                    </div>
                                    @if($homeOptions->isNotEmpty())
                                        <span class="text-sm font-semibold text-gray-700">From {{ number_format($homeOptions->min('price'), 2) }} MAD</span>
                                    @else
                                        <span class="text-xs text-gray-400">Not available</span>
                                    @endif
                                </label>

                                <!-- Sub-options for Home -->
                                <div class="bg-gray-50 pl-12 pr-4 py-2 space-y-2 {{ $homeOptions->isEmpty() ? 'hidden' : '' }} category-options" id="options-home" style="display: none;">
                                    @foreach($homeOptions as $option)
                                        <label class="flex items-center justify-between py-2 cursor-pointer">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="shipping_option_id" value="{{ $option->id }}" 
                                                    data-price="{{ $option->price }}"
                                                    data-category="home"
                                                    class="w-4 h-4 text-vinted-teal border-gray-300 focus:ring-vinted-teal shipping-option-radio">
                                                <div class="flex items-center gap-2">
                                                    @if($option->icon_class)<div class="w-2 h-2 rounded-full {{ $option->icon_class }}"></div>@endif
                                                    <span class="text-sm text-gray-700">{{ $option->label }}</span>
                                                </div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($option->price, 2) }} MAD</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <input type="hidden" id="fallback_shipping" value="{{ $deliveryFeeFixed }}">
                @endif

                <!-- 4. Payment -->
                <div>
                    <h3 class="font-bold text-gray-900 mb-2">Payment</h3>
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden divide-y divide-gray-100">
                        <!-- Wallet -->
                        <label class="flex items-center gap-3 p-4 hover:bg-gray-50 cursor-pointer" id="wallet-option-label">
                            <input type="radio" name="payment_method" value="wallet" id="payment_wallet"
                                class="w-5 h-5 text-vinted-teal border-gray-300 focus:ring-vinted-teal">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                    My Wallet <span id="wallet-error" class="text-xs text-red-500 font-normal hidden">(Insufficient balance)</span>
                                </p>
                                <p class="text-xs text-gray-500">Balance: {{ number_format($walletBalance, 2) }} MAD</p>
                            </div>
                        </label>
                        <!-- COD -->
                        <label class="flex items-center gap-3 p-4 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="cod" checked
                                class="w-5 h-5 text-vinted-teal border-gray-300 focus:ring-vinted-teal">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                                    Cash on Delivery
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Right Column (Sticky Summary) -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-lg border border-gray-200 p-6 sticky top-6">
                    <h3 class="font-bold text-gray-900 mb-4 border-b border-gray-100 pb-3">Price summary</h3>
                    <div class="space-y-3 text-sm mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Order</span>
                            <span class="font-medium text-gray-900">{{ number_format($price, 2) }} MAD</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Buyer Protection fee</span>
                            <span class="font-medium text-gray-900">{{ number_format($protectionFee, 2) }} MAD</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium text-gray-900" id="summary-shipping">-- MAD</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-100 pt-4 mb-6">
                        <span class="font-bold text-lg text-gray-900">Total to pay</span>
                        <span class="font-bold text-lg text-gray-900" id="summary-total">-- MAD</span>
                    </div>
                    <button type="submit" id="submit-btn"
                        class="w-full bg-vinted-teal text-white font-bold py-3 rounded-md hover:bg-vinted-teal-dark transition-colors mb-3">
                        Place order
                    </button>
                    <p class="text-xs text-center text-gray-400 flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Your payment details are encrypted and secure
                    </p>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const basePrice = parseFloat(document.getElementById('base_price').value);
            const protectionFee = parseFloat(document.getElementById('protection_fee').value);
            const walletBalance = parseFloat(document.getElementById('wallet_balance').value);
            
            const categoryRadios = document.querySelectorAll('.category-radio');
            const shippingRadios = document.querySelectorAll('.shipping-option-radio');
            const categoryOptionsContainers = document.querySelectorAll('.category-options');
            
            const summaryShipping = document.getElementById('summary-shipping');
            const summaryTotal = document.getElementById('summary-total');
            const walletOption = document.getElementById('payment_wallet');
            const walletError = document.getElementById('wallet-error');
            const walletOptionLabel = document.getElementById('wallet-option-label');
            const submitBtn = document.getElementById('submit-btn');

            function updateUIState() {
                // 1. Handle Categories visibility
                const selectedCategory = document.querySelector('input[name="delivery_category"]:checked');
                
                categoryOptionsContainers.forEach(el => el.style.display = 'none'); // Hide all first
                
                if (selectedCategory) {
                    const targetId = 'options-' + selectedCategory.value;
                    const container = document.getElementById(targetId);
                    if (container) {
                        container.style.display = 'block';
                        
                        // Enforce selection within category if none selected
                        const currentSelection = container.querySelector('input[name="shipping_option_id"]:checked');
                        if (!currentSelection) {
                            const firstOption = container.querySelector('input[name="shipping_option_id"]');
                            if (firstOption) {
                                firstOption.checked = true;
                            }
                        }
                    }
                }
                
                updateTotals();
            }

            function updateTotals() {
                let shippingCost = 0;
                // Find selected shipping (must be visible/active category?)
                // Actually just find the checked one, but ensure it belongs to checked category?
                // The radio grouping 'shipping_option_id' is global, implying only one can be checked across all categories.
                // However, switching categories might leave a radio checked in a hidden category if we are not careful.
                
                // Best approach: Find checked shipping radio. If it's in a hidden container, ignore it? 
                // No, when switching category, we force check the first one in visible container. So checking 'active' is safe.
                
                const selected = document.querySelector('input[name="shipping_option_id"]:checked');
                if (selected) {
                    shippingCost = parseFloat(selected.getAttribute('data-price'));
                } else {
                    // Fallback
                    const fallback = document.getElementById('fallback_shipping');
                    if(fallback) shippingCost = parseFloat(fallback.value);
                }

                const total = basePrice + protectionFee + shippingCost;
                
                // Update UI
                summaryShipping.textContent = shippingCost.toFixed(2) + ' MAD';
                summaryTotal.textContent = total.toFixed(2) + ' MAD';

                // Check wallet validity
                if (walletBalance >= total) {
                    walletOption.disabled = false;
                    walletError.classList.add('hidden');
                    walletOptionLabel.classList.remove('opacity-60', 'cursor-not-allowed');
                    walletOptionLabel.classList.add('cursor-pointer', 'hover:bg-gray-50');
                } else {
                    walletOption.disabled = true;
                    if (walletOption.checked) {
                        // Switch to COD
                        const cod = document.querySelector('input[value="cod"]');
                        if(cod) cod.checked = true;
                    }
                    walletError.classList.remove('hidden');
                    walletOptionLabel.classList.add('opacity-60', 'cursor-not-allowed');
                    walletOptionLabel.classList.remove('cursor-pointer', 'hover:bg-gray-50');
                }
            }

            // Listeners
            categoryRadios.forEach(radio => {
                radio.addEventListener('change', updateUIState);
            });
            
            shippingRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // When clicking a specific option, ensure parent category is checked (it should be allowed naturally)
                    const category = this.getAttribute('data-category');
                    const categoryRadio = document.querySelector(`input[name="delivery_category"][value="${category}"]`);
                    if(categoryRadio && !categoryRadio.checked) {
                        categoryRadio.checked = true;
                        updateUIState(); // This might re-select first option? No, check logic.
                        // updateUIState checks if selection exists. If we just clicked it, it exists.
                    } else {
                        updateTotals();
                    }
                });
            });

            // Initial calculation
            // Force selection if none
            if (!document.querySelector('input[name="delivery_category"]:checked')) {
               // Try to check first available
               const first = document.querySelector('input[name="delivery_category"]:not(:disabled)');
               if(first) first.checked = true;
            }
            updateUIState();
        });
    </script>
@endsection
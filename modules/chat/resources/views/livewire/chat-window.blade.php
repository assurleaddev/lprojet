<div class="flex flex-col h-full" x-data="chatWindow({{ $conversationId }})" {{-- Robust Alpine.js Pusher/Echo Listener
    Bridge --}} x-init="() => {
        console.log('[Alpine x-init] Initializing for conversation {{ $conversationId }}');

        const setupEchoListener = () => {
            // Check if Echo and necessary Pusher connection state exist
            if (window.Echo && window.Echo.connector && window.Echo.connector.pusher && window.Echo.connector.pusher.connection) {
                const connectionState = window.Echo.connector.pusher.connection.state;
                console.log('[Alpine x-init] Echo exists. Pusher connection state:', connectionState);

                if (connectionState === 'connected') {
                    console.log('[Alpine x-init] Echo is connected. Attaching listener...');

                    // Ensure we leave any potential old channel first (defensive programming)
                    window.Echo.leave('conversations.{{ $conversationId }}');

                    // Attach the listener to the private channel
                    window.Echo.private('conversations.{{ $conversationId }}')
                        .listen('.new-message', (e) => { // Use the exact broadcastAs name 'new-message' with leading dot
                            console.log('[Alpine Echo Listener] Received new-message event via Echo:', e);

                            // Validate incoming event structure (basic)
                            if (e && e.user && typeof e.user.id !== 'undefined') {
                                // Check if the message is NOT from the currently authenticated user
                                if (e.user.id !== {{ auth()->id() }}) {
                                    console.log('[Alpine Echo Listener] Dispatching refresh-chat Livewire event.');
                                    // Dispatch the standard Livewire event to trigger PHP refreshMessages method
                                    $wire.dispatch('refresh-chat');
                                } else {
                                    console.log('[Alpine Echo Listener] Ignoring own message broadcast.');
                                }
                            } else {
                                console.warn('[Alpine Echo Listener] Received invalid event data structure:', e);
                            }
                        });

                    console.log('[Alpine x-init] Echo listener attached successfully for conversations.{{ $conversationId }}');

                } else {
                    // If Echo isn't connected yet, wait and try again
                    console.warn(`[Alpine x-init] Echo not connected yet (State: ${connectionState}). Retrying listener setup in 1000ms...`);
                    setTimeout(setupEchoListener, 1000); // Retry after 1 second
                }
            } else {
                 // If Echo or Pusher connector isn't fully initialized, wait and try again
                console.error('[Alpine x-init] Laravel Echo or Pusher connector not fully initialized. Retrying in 1000ms...');
                setTimeout(setupEchoListener, 1000); // Retry after 1 second
            }
        };

        // Initial call to set up the listener
        setupEchoListener();

     }" {{-- Add wire:key to help Livewire identify this component instance if multiple could exist --}}
    wire:key="chat-window-{{ $conversationId }}">

    {{-- 1. Header --}}
    <div class="px-6 py-4 border-b dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex-shrink-0">
        @if($conversation)
            @php $otherUser = $conversation->getOtherUser(auth()->user()); @endphp

            <div class="flex items-center justify-between">
                {{-- Left: User Info --}}
                <div class="flex items-center space-x-3">
                    <h3 class="font-bold text-lg text-teal-600">
                        {{ optional($otherUser)->full_name ?? 'User Not Found' }}
                    </h3>
                </div>

                {{-- Right: Info Icon --}}
                <button class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
            </div>

            {{-- Product Info Bar --}}
            <div class="mt-4 flex items-start space-x-4 border-t pt-4">
                <div class="flex-shrink-0">
                    <img src="{{ $conversation->product->getFeaturedImageUrl('preview') }}"
                        alt="{{ $conversation->product->name }}" class="w-12 h-16 object-cover rounded-md bg-gray-100">
                </div>

                <div class="flex-1">
                    <h4 class="font-medium text-gray-900">{{ $conversation->product->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $conversation->product->price }} MAD</p>
                    <p class="text-xs text-teal-600 flex items-center mt-1">
                        {{ number_format($conversation->product->price * 1.05 + 10, 2) }} MAD Includes Buyer Protection
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </p>
                </div>

                <div class="flex space-x-2">
                    <button
                        @click="Livewire.dispatch('open-make-offer-modal', { productId: {{ $conversation->product->id }} })"
                        class="px-4 py-2 border border-teal-600 text-teal-600 font-medium rounded-md hover:bg-teal-50 text-sm">
                        Make an offer
                    </button>
                    <a href="{{ route('product.checkout', $conversation->product) }}"
                        class="px-4 py-2 bg-teal-700 text-white font-medium rounded-md hover:bg-teal-800 text-sm">
                        Buy now
                    </a>
                </div>
            </div>
        @else
            <h3 class="font-semibold text-lg text-gray-400">Select a conversation</h3>
        @endif
    </div>

    {{-- 2. Message Area (Scrollable) --}}
    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4 bg-white dark:bg-gray-900" x-ref="messageContainer">
        @if($conversation)
            {{-- System Message --}}
            @php $otherUser = $conversation->getOtherUser(auth()->user()); @endphp
            <div class="flex justify-start mb-6">
                <div class="flex items-start space-x-3 max-w-lg">
                    <img src="{{ $otherUser->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->full_name) }}"
                        alt="{{ $otherUser->full_name }}" class="w-10 h-10 rounded-full object-cover">
                    <div
                        class="bg-gray-100 dark:bg-gray-800 rounded-2xl rounded-tl-none p-4 text-sm text-gray-800 dark:text-gray-200">
                        <p>Hi, I'm {{ $otherUser->full_name }}</p>
                        <div class="mt-2 text-xs text-gray-500 space-y-1">
                            <p class="flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg> {{ $otherUser->city ?? 'Location not set' }}</p>
                            <p class="flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg> Last seen
                                {{ $otherUser->last_seen_at ? \Carbon\Carbon::parse($otherUser->last_seen_at)->diffForHumans() : 'recently' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @forelse($messages as $key => $message)
                {{-- Robust data access & preparation --}}
                @php
                    $messageData = (object) $message;
                    $messageType = $messageData->type ?? 'text';
                    $messageId = $messageData->id ?? ('rand_' . rand()); // Use real ID or random fallback for key
                    $offerData = isset($messageData->offer) ? (object) $messageData->offer : null;
                    $offerId = $offerData->id ?? null;
                    // Correctly try to create Enum instance from status string
                    $offerStatus = $offerData ? \Modules\Chat\Enums\OfferStatus::tryFrom($offerData->status ?? '') : null;
                    $productData = $offerData ? (object) ($offerData->product ?? null) : null; // Access product through offer data
                    // Access the pre-calculated URL added in the PHP component's map function
                    $featuredImageUrl = $productData->featured_image_url ?? null;

                    // Standard message details
                    $messageUserId = $messageData->user['id'] ?? ($messageData->user_id ?? null);
                    $messageBody = $messageData->body ?? '';
                    $messageTime = isset($messageData->created_at) ? (\Carbon\Carbon::parse($messageData->created_at)->format('H:i')) : ($messageData->created_at_human ?? 'Just now');
                    $isOwnMessage = $messageUserId == auth()->id();
                @endphp

                {{-- Differentiate rendering based on message type --}}
                @if((str_starts_with($messageType, 'offer_') && $messageType !== 'offer_checkout_prompt') && $offerData && $productData)
                    {{-- OFFER MESSAGE BLOCK --}}
                    <div wire:key="offer-msg-{{ $messageId }}-{{ $offerStatus?->value ?? 'unknown' }}">
                        <div class="flex {{ $isOwnMessage ? 'justify-end' : 'justify-start' }}">
                            {{-- Vinted-style Compact Card --}}
                            <div
                                class="w-full max-w-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">

                                {{-- Body: Prices & Image --}}
                                <div class="p-4 flex items-center justify-between">
                                    <div>
                                        <div class="flex items-baseline space-x-2">
                                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ number_format($offerData->offer_price ?? 0, 2) }} MAD
                                            </span>
                                            <span class="text-sm text-gray-400 line-through">
                                                {{ number_format($productData->price ?? 0, 2) }} MAD
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $offerStatus?->value }}</p>
                                    </div>
                                    {{-- Optional: Small Product Thumbnail --}}
                                    <img src="{{ $featuredImageUrl ?? asset('images/default.svg') }}" alt="Product"
                                        class="w-10 h-10 rounded object-cover border border-gray-100">
                                </div>

                                {{-- Footer: Actions --}}
                                {{-- 1. Vendor receiving Pending Offer --}}
                                @if($messageType === 'offer_made' && $offerStatus === \Modules\Chat\Enums\OfferStatus::Pending && !$isOwnMessage && $offerId)
                                    <div
                                        class="p-2 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-2">
                                        <button wire:click="acceptOffer({{ $offerId }})" wire:loading.attr="disabled"
                                            class="col-span-2 w-full bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium py-2 rounded-md transition-colors">
                                            Accept
                                        </button>
                                        <button wire:click="triggerCounterOffer({{ $productData->id }}, {{ $offerData->buyer_id }})"
                                            wire:loading.attr="disabled" type="button"
                                            class="w-full bg-white border border-teal-600 text-teal-600 hover:bg-teal-50 text-sm font-medium py-2 rounded-md transition-colors">
                                            Counter
                                        </button>
                                        <button wire:click="promptRejectOffer({{ $offerId }})" wire:loading.attr="disabled"
                                            class="w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium py-2 rounded-md transition-colors">
                                            Decline
                                        </button>
                                    </div>

                                    {{-- 2. Buyer receiving Counter Offer --}}
                                @elseif($messageType === 'offer_countered' && $offerStatus === \Modules\Chat\Enums\OfferStatus::AwaitingBuyer && !$isOwnMessage && $offerId)
                                    <div
                                        class="p-2 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-2">
                                        <button wire:click="acceptOffer({{ $offerId }})" wire:loading.attr="disabled"
                                            class="col-span-2 w-full bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium py-2 rounded-md transition-colors">
                                            Accept {{ number_format($offerData->offer_price ?? 0, 2) }} MAD
                                        </button>
                                        <button wire:click="promptRejectOffer({{ $offerId }})" wire:loading.attr="disabled"
                                            class="col-span-2 w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium py-2 rounded-md transition-colors">
                                            Decline
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                @elseif ($messageType === 'item_sold')
                    @php
                        // Extract URL from body
                        preg_match('/(https?:\/\/[^\s]+)/', $messageBody, $matches);
                        $downloadUrl = $matches[0] ?? '#';
                        $cleanBody = str_replace($downloadUrl, '', $messageBody);
                    @endphp
                    <div wire:key="item-sold-{{ $messageId }}" class="flex justify-center my-4">
                        <div
                            class="w-full max-w-sm bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-700 rounded-lg shadow-sm overflow-hidden">
                            <div class="p-4 bg-teal-50 dark:bg-teal-900/20 text-center">
                                <div
                                    class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-teal-100 dark:bg-teal-800 mb-3">
                                    <svg class="h-6 w-6 text-teal-600 dark:text-teal-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-teal-900 dark:text-teal-100">Item Sold!</h3>
                                <p class="mt-1 text-sm text-teal-700 dark:text-teal-300">
                                    {{ trim($cleanBody) }}
                                </p>
                                <div class="mt-4 space-y-2">
                                    <a href="{{ $downloadUrl }}" target="_blank"
                                        class="block w-full bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors shadow-sm">
                                        Download Shipping Label
                                    </a>
                                    @if(auth()->id() == $conversation->product->vendor_id)
                                        <button wire:click="markAsShipped" wire:loading.attr="disabled"
                                            class="block w-full bg-white border border-teal-600 text-teal-600 hover:bg-teal-50 text-sm font-medium py-2 px-4 rounded-md transition-colors shadow-sm">
                                            Mark as Shipped
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif ($messageType === 'item_shipped')
                    <div wire:key="item-shipped-{{ $messageId }}" class="flex justify-center my-4">
                        <div
                            class="w-full max-w-sm bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 rounded-lg shadow-sm overflow-hidden">
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 text-center">
                                <div
                                    class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-800 mb-3">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100">Item Shipped!</h3>
                                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                    {{ $messageBody }}
                                </p>

                                @php
                                    // Check if the order is already completed to hide buttons
                                    $buyerId = auth()->id() == $conversation->product->vendor_id
                                        ? ($conversation->user_one_id == auth()->id() ? $conversation->user_two_id : $conversation->user_one_id)
                                        : auth()->id();

                                    $latestOrder = \App\Models\Order::where('product_id', $conversation->product_id)
                                        ->where('user_id', $buyerId)
                                        ->latest()
                                        ->first();

                                    $isOrderCompleted = $latestOrder && $latestOrder->status === 'completed';
                                @endphp

                                @if(!$isOrderCompleted)
                                    <div class="mt-4 grid grid-cols-2 gap-2">
                                        <button wire:click="markAsReceived(0)" wire:loading.attr="disabled"
                                            class="w-full bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors shadow-sm">
                                            Item Received
                                        </button>
                                        <button
                                            wire:click="$dispatch('toast', {message: 'Please contact support for refunds.', type: 'info'})"
                                            class="w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium py-2 px-4 rounded-md transition-colors shadow-sm">
                                            Refund
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- NEW BLOCK: Render the Checkout Prompt specifically --}}
                @elseif ($messageType === 'offer_checkout_prompt' && $offerData && $offerId)
                    <div wire:key="offer-checkout-{{ $messageId }}">
                        {{-- Only show button to the Buyer (who originally made the offer OR accepted the counter offer) --}}
                        @if (auth()->id() == $offerData->buyer_id)
                            <div class="flex justify-start"> {{-- Align left for buyer prompt --}}
                                <div
                                    class="w-full max-w-sm bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-700 rounded-lg shadow-sm overflow-hidden">
                                    <div class="p-4 bg-teal-50 dark:bg-teal-900/20">
                                        <div class="flex items-center mb-3">
                                            <div class="flex-shrink-0 bg-teal-100 dark:bg-teal-800 rounded-full p-2">
                                                <i class="fas fa-shopping-bag text-teal-600 dark:text-teal-300"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-teal-900 dark:text-teal-100">
                                                    {{ $offerStatus === \Modules\Chat\Enums\OfferStatus::AwaitingBuyer ? 'Seller sent a counter offer!' : 'Offer Accepted!' }}
                                                </h3>
                                                <div class="text-xs text-teal-700 dark:text-teal-300">
                                                    You can now purchase this item for
                                                    ${{ number_format($offerData->offer_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        </div>

                                        @php $checkoutRoute = route('checkout.offer', ['offer' => $offerId]); @endphp
                                        <a href="{{ $checkoutRoute }}" wire:navigate
                                            class="block w-full text-center bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors shadow-sm">
                                            Buy Now
                                        </a>
                                    </div>
                                    <div
                                        class="px-4 py-2 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 text-right">
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                            {{ $messageTime }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Show a simple confirmation for the seller --}}
                            <div class="flex justify-end">
                                <div
                                    class="max-w-xs px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs italic">
                                    Checkout link sent to buyer.
                                </div>
                            </div>
                        @endif
                    </div>

                @else
                    {{-- STANDARD TEXT MESSAGE BLOCK --}}
                    <div class="flex {{ $isOwnMessage ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $messageId }}">
                        {{-- Standard message bubble --}}
                        <div
                            class="max-w-xs md:max-w-md lg:max-w-lg px-4 py-2 rounded-lg shadow {{ $isOwnMessage ? 'bg-teal-600 text-white' : 'bg-white dark:bg-gray-700 dark:text-gray-200' }}">
                            {{-- Use linkify helper if you defined it, otherwise just display body --}}
                            <p class="text-sm break-words whitespace-pre-wrap">
                                {!! isset($messageBody) ? (function_exists('linkify') ? linkify($messageBody) : nl2br(e($messageBody))) : '' !!}
                            </p>
                            <span
                                class="text-xs {{ $isOwnMessage ? 'text-teal-100' : 'text-gray-500 dark:text-gray-400' }} mt-1 block text-right">
                                {{ $messageTime }}
                            </span>
                        </div>
                    </div>
                @endif

            @empty
                <p class="text-center text-gray-500 pt-10">No messages yet. Be the first!</p>
            @endforelse
        @else
            {{-- Placeholder when no conversation is selected or loaded --}}
            <p class="text-center text-gray-500 pt-10">Select a conversation from the list.</p>
        @endif
    </div>

    {{-- 3. Message Input Form --}}
    <div class="px-6 py-4 border-t dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0">
        {{-- Safety Banner --}}
        <div class="mb-4 bg-gray-50 dark:bg-gray-700 p-3 rounded-md flex items-start space-x-3">
            <svg class="w-5 h-5 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                </path>
            </svg>
            <div class="text-xs text-gray-600 dark:text-gray-300">
                <span class="font-semibold">Stay safe on Vinted.</span> Don't share personal data, click on external
                links, or scan QR codes.
                <a href="#" class="text-teal-600 hover:underline">More safety tips</a>
            </div>
            <button class="text-gray-400 hover:text-gray-600 ml-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
            <button type="button" class="p-2 text-gray-400 hover:text-gray-600 border border-gray-300 rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button type="button" class="p-2 text-gray-400 hover:text-gray-600 border border-gray-300 rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>

            <div class="flex-1 relative">
                <input type="text" wire:model="messageBody" placeholder="Write a message here"
                    class="w-full bg-gray-100 dark:bg-gray-700 border-none rounded-full py-2.5 px-4 focus:ring-0 text-sm dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    autocomplete="off" @if(!$conversation) disabled @endif>
                <button type="submit"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-teal-600 disabled:opacity-50"
                    wire:loading.attr="disabled" @if(!$conversation || empty($messageBody)) disabled @endif>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
        @error('messageBody') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Rejection Reason Modal (controlled by Livewire showRejectionModal) --}}
    <div x-data="{ show: @entangle('showRejectionModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" {{-- Increased z-index --}}
        aria-labelledby="rejection-modal-title" role="dialog" aria-modal="true">

        <div
            class="flex items-end sm:items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Centering --}}
            {{-- Background overlay --}}
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-500 opacity-75 dark:bg-gray-900 dark:bg-opacity-80 z-[110]"
                {{-- Higher z-index for overlay --}} @click.self="show = false" {{-- Close on overlay click --}}
                aria-hidden="true"></div>

            {{-- Modal panel --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span> {{--
            Vertical centering helper --}}
            <div x-show="show" x-trap.inert.noscroll="show" {{-- Trap focus --}} @click.stop {{-- Stop click propagation
                --}} x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg sm:align-middle z-[120]">
                {{-- Highest z-index for panel --}}

                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="rejection-modal-title">
                    Reason for Rejection
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Please provide a reason for rejecting the
                    offer.</p>

                <form wire:submit.prevent="rejectOffer" class="mt-4 space-y-4">
                    <div>
                        <label for="rejectionReasonInputModal" class="sr-only">Rejection Reason</label> {{-- Unique ID
                        --}}
                        <textarea wire:model="rejectionReason" id="rejectionReasonInputModal" rows="4" {{-- Unique ID
                            --}}
                            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200"
                            placeholder="Enter reason (min 10 characters)..." required></textarea>
                        @error('rejectionReason') <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-4 border-t dark:border-gray-600"> {{-- Added
                        padding/border --}}
                        <button type="button" @click="show = false" wire:click="closeRejectionModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="rejectOffer"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                            <span wire:loading wire:target="rejectOffer" class="animate-pulse">Rejecting...</span>
                            <span wire:loading.remove wire:target="rejectOffer">Confirm Rejection</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Review Modal --}}
    <div x-data="{ show: @entangle('showReviewModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="review-modal-title"
        role="dialog" aria-modal="true">

        <div
            class="flex items-end sm:items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-500 opacity-75 dark:bg-gray-900 dark:bg-opacity-80 z-[110]"
                @click.self="show = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-trap.inert.noscroll="show" @click.stop x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg sm:align-middle z-[120]">

                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="review-modal-title">
                    Leave a Review
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Everything good? Rate your experience.</p>

                <form wire:submit.prevent="submitReview" class="mt-4 space-y-4">
                    {{-- Star Rating --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating</label>
                        <div class="flex items-center space-x-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="$set('reviewRating', {{ $i }})"
                                    class="focus:outline-none">
                                    <svg class="w-8 h-8 {{ $reviewRating >= $i ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        @error('reviewRating') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Review Text --}}
                    <div>
                        <label for="reviewTextInput"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Review (Optional)</label>
                        <textarea wire:model="reviewText" id="reviewTextInput" rows="3"
                            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200"
                            placeholder="Describe your experience..."></textarea>
                        @error('reviewText') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-4 border-t dark:border-gray-600">
                        <button type="button" @click="show = false" wire:click="closeReviewModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="submitReview"
                            class="px-4 py-2 text-sm font-medium text-white bg-teal-600 border border-transparent rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50">
                            <span wire:loading wire:target="submitReview" class="animate-pulse">Submitting...</span>
                            <span wire:loading.remove wire:target="submitReview">Submit Review</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<div class="flex flex-col h-full"
     x-data="chatWindow({{ $conversationId }})"

     {{-- Robust Alpine.js Pusher/Echo Listener Bridge --}}
     x-init="() => {
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

    }"
    wire:key="chat-window-{{ $conversationId }}"
>

    {{-- 1. Header --}}
    <div class="p-4 border-b dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex-shrink-0">
        @if($conversation)
            {{-- Safely access relationships loaded in loadConversation --}}
            @php $otherUser = $conversation->getOtherUser(auth()->user()); @endphp
            <h3 class="font-semibold text-lg">{{ $otherUser->name ?? 'User Not Found' }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                Regarding: {{ $conversation->product->name ?? 'Product Deleted' }}
            </p>
        @else
             <h3 class="font-semibold text-lg text-gray-400">Select a conversation</h3>
             {{-- Or show an error if conversationId was invalid --}}
             {{-- <p class="text-red-500">Could not load conversation.</p> --}}
        @endif
    </div>

    {{-- 2. Message Area (Scrollable) --}}
    <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900" x-ref="messageContainer">
        @if($conversation)
            @forelse($messages as $key => $message)
                {{-- Robust data access & preparation --}}
                @php
                    $messageData = (object) $message;
                    $messageType = $messageData->type ?? 'text';
                    $messageId = $messageData->id ?? ('rand_'.rand()); // Use real ID or random fallback for key
                    $offerData = isset($messageData->offer) ? (object) $messageData->offer : null;
                    $offerId = $offerData->id ?? null;
                    $offerStatus = $offerData ? (\Modules\Chat\Enums\OfferStatus::tryFrom($offerData->status ?? '') ?? null) : null;
                    $productData = $offerData ? (object) ($offerData->product ?? null) : null; // Access product through offer data
                    $featuredImageUrl = $productData->featured_image_url ?? null; // Access pre-calculated URL

                    // Standard message details
                    $messageUserId = $messageData->user['id'] ?? ($messageData->user_id ?? null);
                    $messageBody = $messageData->body ?? '';
                    $messageTime = isset($messageData->created_at) ? (\Carbon\Carbon::parse($messageData->created_at)->diffForHumans()) : ($messageData->created_at_human ?? 'Just now');
                    $isOwnMessage = $messageUserId == auth()->id();
                @endphp

                {{-- Differentiate rendering based on message type --}}
                @if(str_starts_with($messageType, 'offer_') && $offerData && $productData)
                    {{-- OFFER MESSAGE BLOCK --}}
                    <div class="flex {{ $isOwnMessage ? 'justify-end' : 'justify-start' }}" wire:key="offer-msg-{{ $messageId }}">
                        <div class="w-full max-w-sm md:max-w-md p-4 rounded-lg shadow border {{ $isOwnMessage ? 'bg-green-50 dark:bg-green-900 border-green-200 dark:border-green-700' : 'bg-yellow-50 dark:bg-yellow-900 border-yellow-200 dark:border-yellow-700' }}">

                            {{-- Featured Image --}}
                            <img src="{{ $featuredImageUrl ?? asset('images/default.svg') }}" {{-- Use the pre-calculated URL --}}
                                 alt="{{ $productData->name ?? 'Product' }}"
                                 class="w-24 h-24 object-cover rounded mb-3 mx-auto border dark:border-gray-600">

                            <p class="text-center font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ $productData->name ?? 'Product Name' }}</p>

                            {{-- Price Details --}}
                            <div class="text-center text-sm mb-3">
                                <p class="text-gray-500 dark:text-gray-400">Original Price: <span class="font-medium text-gray-700 dark:text-gray-300">${{ number_format($productData->price ?? 0, 2) }}</span></p>
                                <p class="text-green-600 dark:text-green-400">Offer Price: <span class="font-bold text-lg">${{ number_format($offerData->offer_price ?? 0, 2) }}</span></p>
                            </div>

                            {{-- System Message Body (Optional, can replace status badge sometimes) --}}
                             @if ($messageBody)
                                 <p class="text-center text-xs text-gray-600 dark:text-gray-400 mb-2 italic">"{{ $messageBody }}"</p>
                             @endif


                            {{-- Offer Status Badge / Info --}}
                            <div class="text-center mb-4">
                                @switch($offerStatus)
                                    @case(\Modules\Chat\Enums\OfferStatus::Pending)
                                        <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-200 dark:bg-yellow-700 dark:text-yellow-100 rounded-full">Pending Seller Response</span>
                                        @break
                                    @case(\Modules\Chat\Enums\OfferStatus::Accepted)
                                        <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-200 dark:bg-green-700 dark:text-green-100 rounded-full">Offer Accepted</span>
                                        @break
                                    @case(\Modules\Chat\Enums\OfferStatus::Rejected)
                                        <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-200 dark:bg-red-700 dark:text-red-100 rounded-full">Offer Rejected</span>
                                        @if($offerData->rejection_reason)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">Reason: {{ $offerData->rejection_reason }}</p>
                                        @endif
                                        @break
                                    @default
                                        <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-200 dark:bg-gray-700 dark:text-gray-100 rounded-full">Status Unknown</span>
                                @endswitch
                            </div>

                            {{-- Action Buttons (Only for Seller on Pending Offers) --}}
                            @if ($offerStatus === \Modules\Chat\Enums\OfferStatus::Pending && auth()->id() == $offerData->seller_id && $offerId)
                                <div class="flex justify-center space-x-3 mt-3 border-t pt-3 dark:border-gray-600">
                                    <button type="button"
                                            wire:click="acceptOffer({{ $offerId }})"
                                            wire:loading.attr="disabled" wire:target="acceptOffer({{ $offerId }})"
                                            class="px-3 py-1 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                        <span wire:loading wire:target="acceptOffer({{ $offerId }})">...</span>
                                        <span wire:loading.remove wire:target="acceptOffer({{ $offerId }})">Accept</span>
                                    </button>
                                    <button type="button"
                                            wire:click="promptRejectOffer({{ $offerId }})"
                                            wire:loading.attr="disabled" wire:target="promptRejectOffer({{ $offerId }})"
                                            class="px-3 py-1 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                                         <span wire:loading wire:target="promptRejectOffer({{ $offerId }})">...</span>
                                         <span wire:loading.remove wire:target="promptRejectOffer({{ $offerId }})">Reject</span>
                                    </button>
                                </div>
                            @endif

                            {{-- Timestamp --}}
                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 block text-center">
                                 {{ $messageTime }}
                            </span>
                        </div>
                    </div>

                @else
                    {{-- STANDARD TEXT MESSAGE BLOCK --}}
                    <div class="flex {{ $isOwnMessage ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $messageId }}">
                        <div class="max-w-xs md:max-w-md lg:max-w-lg px-4 py-2 rounded-lg shadow {{ $isOwnMessage ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-700 dark:text-gray-200' }}">
                            <p class="text-sm break-words whitespace-pre-wrap">{{ $messageBody }}</p> {{-- Added whitespace-pre-wrap --}}
                            <span class="text-xs {{ $isOwnMessage ? 'text-blue-200' : 'text-gray-500 dark:text-gray-400' }} mt-1 block text-right">
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
    <div class="p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0">
        <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
            <input type="text"
                   wire:model="messageBody"
                   placeholder="Type your message..."
                   class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                   autocomplete="off"
                   {{-- Disable input if conversation isn't loaded --}}
                   @if(!$conversation) disabled @endif
                   >
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    {{-- Disable button if conversation isn't loaded or message is empty --}}
                    @if(!$conversation || empty($messageBody)) disabled @endif
                    >
                {{-- Loading state specific to sendMessage action --}}
                <span wire:loading wire:target="sendMessage" class="animate-pulse">...</span>
                <span wire:loading.remove wire:target="sendMessage">Send</span>
            </button>
        </form>
        @error('messageBody') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Rejection Reason Modal (controlled by Livewire showRejectionModal) --}}
    <div x-data="{ show: @entangle('showRejectionModal') }"
         x-show="show"
         x-on:keydown.escape.window="show = false"
         style="display: none;"
         class="fixed inset-0 z-[100] overflow-y-auto" {{-- Increased z-index --}}
         aria-labelledby="rejection-modal-title" role="dialog" aria-modal="true">

        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div x-show="show"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-80" {{-- Darker overlay --}}
                 @click="show = false" aria-hidden="true"></div>

            {{-- Modal panel --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="show"
                 x-trap.inert.noscroll="show" {{-- Trap focus and prevent body scroll --}}
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg sm:align-middle">

                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="rejection-modal-title">
                    Reason for Rejection
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Please provide a reason for rejecting the offer.</p>

                <form wire:submit.prevent="rejectOffer" class="mt-4 space-y-4">
                    <div>
                        <label for="rejectionReasonInput" class="sr-only">Rejection Reason</label> {{-- Changed ID to avoid conflict --}}
                        <textarea wire:model="rejectionReason" id="rejectionReasonInput" rows="4" {{-- Changed ID --}}
                                  class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200"
                                  placeholder="Enter reason (min 10 characters)..." required></textarea>
                        @error('rejectionReason') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-4 border-t dark:border-gray-600"> {{-- Added padding/border --}}
                        <button type="button" @click="show = false" wire:click="closeRejectionModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled" wire:target="rejectOffer"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                            <span wire:loading wire:target="rejectOffer" class="animate-pulse">Rejecting...</span>
                            <span wire:loading.remove wire:target="rejectOffer">Confirm Rejection</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Alpine JS for Scrolling Logic (Keep as is, ensure definition is loaded) --}}
    <script>
        // Ensure this function is defined globally or properly scoped if using modules
        if (typeof chatWindow !== 'function') {
            function chatWindow(conversationId) {
                return {
                    conversationId: conversationId,
                    init() {
                        console.log(`[Alpine Scroll] Initializing scroll behavior for conversation ${this.conversationId}`);
                        this.scrollToBottom(); // Scroll on initial load

                        // Listen for Livewire events dispatched after message send/receive OR refresh
                        Livewire.on('message-sent', (event) => {
                            // Correctly access conversationId for Livewire 3+ event structure
                            // Check if event is an array and access the first element's properties
                            const eventData = Array.isArray(event) && event.length > 0 ? event[0] : event;
                            if (eventData && eventData.conversationId === this.conversationId) {
                                console.log('[Alpine Scroll] message-sent received, scrolling');
                               this.scrollToBottom();
                            }
                        });
                         Livewire.on('message-received', (event) => {
                            const eventData = Array.isArray(event) && event.length > 0 ? event[0] : event;
                             if (eventData && eventData.conversationId === this.conversationId) {
                                console.log('[Alpine Scroll] message-received received, scrolling');
                                this.scrollToBottom();
                             }
                        });
                    },
                    scrollToBottom() {
                        // Use $nextTick to wait for DOM updates from Livewire/Alpine
                        this.$nextTick(() => {
                            const container = this.$refs.messageContainer;
                            if (container) {
                                container.scrollTop = container.scrollHeight;
                                console.log(`[Alpine Scroll] Scrolled to bottom (scrollHeight: ${container.scrollHeight}) for conversation ${this.conversationId}`);
                            } else {
                                console.warn(`[Alpine Scroll] messageContainer ref not found for conversation ${this.conversationId}`);
                            }
                        });
                    }
                }
            }
        }
    </script>
</div>
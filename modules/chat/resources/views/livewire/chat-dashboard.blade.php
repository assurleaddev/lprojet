<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div
        class="flex h-[calc(100vh-theme(spacing.32))] overflow-hidden bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        {{-- Adjusted height and added container styles --}}

        {{-- 1. Conversation List (Sidebar) --}}
        <div class="w-1/3 border-r border-gray-200 dark:border-gray-700 overflow-y-auto bg-white dark:bg-gray-800">
            <h2 class="text-lg font-bold p-4 border-b dark:border-gray-700 text-gray-900">Inbox</h2>
            @if($this->conversations->isEmpty())
                <p class="p-4 text-gray-500">No conversations yet.</p>
            @else
                <ul>
                    @foreach($this->conversations as $conv)
                        @php $otherUser = $conv->getOtherUser(auth()->user()); @endphp
                        <li wire:click="selectConversation({{ $conv->id }})"
                            class="p-4 border-b dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $selectedConversationId === $conv->id ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
                            wire:key="conversation-{{ $conv->id }}">

                            <div class="flex items-start space-x-3">
                                {{-- User Avatar --}}
                                @if($otherUser->avatar_id)
                                    <img src="{{ $otherUser->avatar_url }}"
                                        alt="{{ $otherUser->full_name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-teal-600 flex-shrink-0 flex items-center justify-center text-white font-bold text-sm">
                                        {{ $otherUser->initials }}
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $otherUser->full_name ?? 'Unknown User' }}
                                        </h3>
                                        <div class="flex flex-col items-end">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans(null, true, true) : '' }}
                                            </span>
                                            @if($conv->has_unread)
                                                <div class="mt-1 w-2.5 h-2.5 bg-red-500 rounded-full shadow-sm" title="Unread messages"></div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 truncate pr-2">
                                            {{ $conv->product->name ?? 'Product Deleted' }}
                                            <br>
                                            <span class="text-gray-500 font-normal">
                                                {{ $conv->product->price ?? '0.00' }} MAD
                                            </span>
                                        </p>

                                        @if($conv->product && $conv->product->getFeaturedImageUrl('preview'))
                                            <img src="{{ $conv->product->getFeaturedImageUrl('preview') }}" alt="Product"
                                                class="w-10 h-10 rounded-md object-cover border border-gray-200">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="w-2/3 flex flex-col bg-gray-50 dark:bg-gray-900" 
            x-data="{ lastRefresh: 0 }"
            x-init="() => {
                if (typeof Echo !== 'undefined') {
                    Echo.private('App.Models.User.{{ auth()->id() }}')
                        .notification((notification) => {
                            // Debounce refresh to avoid collision with ChatWindow's own refresh
                            const now = Date.now();
                            if (now - lastRefresh > 2000) {
                                console.log('[Alpine Dashboard] Notification received, refreshing sidebar (debounced)');
                                $wire.dispatch('refresh-dashboard');
                                lastRefresh = now;
                            }
                        });
                }
            }">
            @if($selectedConversationId)
                {{-- Load the ChatWindow component for the selected conversation --}}
                {{-- Pass the conversation ID to the component --}}
                <livewire:chat::chat-window :conversationId="$selectedConversationId" :key="'chat-window-' . $selectedConversationId" />
            @else
                <div class="flex items-center justify-center h-full">
                    <p class="text-gray-500">Select a conversation to start chatting.</p>
                </div>
            @endif
        </div>

        {{-- Include the Make Offer Modal (Global for this view) --}}
        @livewire('chat::make-offer-modal')
        @livewire('chat::counter-offer-modal')
    </div>
</div>

@push('scripts')
    <script>
        // Scripts removed: Scroll logic is now internalized in the ChatWindow component.
    </script>
@endpush
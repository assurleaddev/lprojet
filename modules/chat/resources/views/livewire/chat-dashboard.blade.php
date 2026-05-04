<style>
    /* Desktop: force sidebar + chat side-by-side, immune to Alpine toggling */
    @media (min-width: 768px) {
        #chat-sidebar {
            display: flex !important;
            flex-direction: column !important;
            width: 33.333333% !important;
        }
        #chat-main {
            display: flex !important;
            flex-direction: column !important;
            width: 66.666667% !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto md:px-4 md:py-6"
    x-data="{ showChat: false }"
    x-init="if (new URLSearchParams(window.location.search).has('id')) showChat = true"
    @conversation-selected.window="showChat = true"
    @back-to-inbox.window="showChat = false">

    <div class="flex h-[100dvh] md:h-[calc(100vh-theme(spacing.32))] overflow-hidden bg-white dark:bg-gray-800 md:rounded-lg md:shadow-sm md:border md:border-gray-200 dark:border-gray-700">

        {{-- 1. Conversation List (Sidebar) --}}
        <div id="chat-sidebar"
            class="border-r border-gray-200 dark:border-gray-700 overflow-y-auto bg-white dark:bg-gray-800 flex-shrink-0"
            :class="showChat ? 'hidden' : 'flex flex-col w-full'">

            <h2 class="text-lg font-bold p-4 border-b dark:border-gray-700 text-gray-900 flex-shrink-0">{{ __('Inbox') }}</h2>

            @if($this->conversations->isEmpty())
                <p class="p-4 text-gray-500">{{ __('No conversations yet.') }}</p>
            @else
                <ul class="flex-1 overflow-y-auto">
                    @foreach($this->conversations as $conv)
                        @php $otherUser = $conv->getOtherUser(auth()->user()); @endphp
                        <li wire:click="selectConversation({{ $conv->id }})"
                            @click="showChat = true"
                            class="p-4 border-b dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $selectedConversationId === $conv->id ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
                            wire:key="conversation-{{ $conv->id }}">

                            @php
                                $isSystem = $otherUser && $otherUser->is_system;
                                $displayName = $isSystem ? config('app.name') : ($otherUser->full_name ?? 'Unknown User');
                                $lastMeta = $isSystem && $conv->lastMessage ? ($conv->lastMessage->metadata ?? []) : [];
                                $broadcastPreview = $lastMeta['pre_text'] ?? ($lastMeta['title'] ?? null);
                            @endphp

                            <div class="flex items-start space-x-3">
                                {{-- User Avatar --}}
                                @if($isSystem)
                                    <img src="{{ $otherUser->avatar_url }}" alt="{{ $displayName }}"
                                        class="w-11 h-11 rounded-full object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                @elseif($otherUser->avatar_id)
                                    <img src="{{ $otherUser->avatar_url }}"
                                        alt="{{ $displayName }}" class="w-11 h-11 rounded-full object-cover flex-shrink-0">
                                @else
                                    <div class="w-11 h-11 rounded-full bg-teal-600 flex-shrink-0 flex items-center justify-center text-white font-bold text-sm">
                                        {{ $otherUser->initials }}
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $displayName }}
                                        </h3>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                {{ $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans(null, true, true) : '' }}
                                            </span>
                                            @if($conv->has_unread)
                                                <div class="w-2.5 h-2.5 bg-red-500 rounded-full shadow-sm flex-shrink-0" title="Unread messages"></div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center mt-1">
                                        @if($isSystem)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate pr-2 italic">
                                                {{ $broadcastPreview ? \Illuminate\Support\Str::limit($broadcastPreview, 40) : config('app.name') }}
                                            </p>
                                        @else
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate pr-2">
                                                {{ $conv->product->name ?? __('Product Deleted') }}
                                                <br>
                                                <span class="text-gray-500 font-normal">
                                                    {{ $conv->product->price ?? '0.00' }} MAD
                                                </span>
                                            </p>

                                            @if($conv->product && $conv->product->getFeaturedImageUrl('preview'))
                                                <img src="{{ $conv->product->getFeaturedImageUrl('preview') }}" alt="Product"
                                                    class="w-10 h-10 rounded-md object-cover border border-gray-200 flex-shrink-0">
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- 2. Chat Window --}}
        <div id="chat-main"
            class="bg-gray-50 dark:bg-gray-900"
            :class="showChat ? 'flex flex-col w-full' : 'hidden'"
            x-data="{ lastRefresh: 0 }"
            x-init="() => {
                if (typeof Echo !== 'undefined') {
                    Echo.private('App.Models.User.{{ auth()->id() }}')
                        .notification((notification) => {
                            const now = Date.now();
                            if (now - lastRefresh > 2000) {
                                $wire.dispatch('refresh-dashboard');
                                lastRefresh = now;
                            }
                        });
                }
            }">
            @if($selectedConversationId)
                <livewire:chat::chat-window :conversationId="$selectedConversationId" :key="'chat-window-' . $selectedConversationId" />
            @else
                <div class="hidden md:flex items-center justify-center h-full">
                    <p class="text-gray-500">{{ __('Select a conversation to start chatting.') }}</p>
                </div>
            @endif
        </div>

        {{-- Modals --}}
        @livewire('chat::make-offer-modal')
        @livewire('chat::counter-offer-modal')
    </div>
</div>

@push('scripts')
    <script>
        // Scripts removed: Scroll logic is now internalized in the ChatWindow component.
    </script>
@endpush
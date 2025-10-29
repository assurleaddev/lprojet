<div class="flex h-[calc(100vh-theme(spacing.16))] overflow-hidden"> {{-- Adjust height based on your layout's header/footer --}}

    {{-- 1. Conversation List (Sidebar) --}}
    <div class="w-1/4 border-r border-gray-200 dark:border-gray-700 overflow-y-auto bg-white dark:bg-gray-800">
        <h2 class="text-lg font-semibold p-4 border-b dark:border-gray-700">Conversations</h2>
        @if($conversations->isEmpty())
            <p class="p-4 text-gray-500">No conversations yet.</p>
        @else
            <ul>
                @foreach($conversations as $conv)
                    <li wire:click="selectConversation({{ $conv->id }})"
                        class="p-4 border-b dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $selectedConversation?->id === $conv->id ? 'bg-blue-100 dark:bg-blue-900' : '' }}"
                        wire:key="conversation-{{ $conv->id }}">
                        
                        {{-- Determine the other user --}}
                        @php $otherUser = $conv->getOtherUser(auth()->user()); @endphp

                        <div class="font-semibold">{{ $otherUser->name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 truncate">
                            {{ $conv->product->name ?? 'Product Deleted' }}
                        </div>
                        {{-- You can add last message preview here later --}}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- 2. Chat Window (Main Area) --}}
    <div class="w-3/4 flex flex-col bg-gray-50 dark:bg-gray-900">
        @if($selectedConversation)
            {{-- Load the ChatWindow component for the selected conversation --}}
            {{-- Pass the conversation ID to the component --}}
             @livewire('chat::chat-window', ['conversationId' => $selectedConversation->id], key($selectedConversation->id))
        @else
            <div class="flex items-center justify-center h-full">
                <p class="text-gray-500">Select a conversation to start chatting.</p>
            </div>
        @endif
    </div>

</div>
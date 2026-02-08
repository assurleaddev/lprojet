<?php

namespace Modules\Chat\Livewire;

use Livewire\Component;
use Modules\Chat\Services\ChatService; // Ensure correct import for your service
use Modules\Chat\Models\Conversation; // Ensure correct import for your Conversation model
use Illuminate\Database\Eloquent\Collection; // Make sure Eloquent Collection is imported
use Illuminate\Support\Facades\Auth; // Import Auth facade

use Livewire\Attributes\Url; // Import Url attribute
use Livewire\Attributes\Layout; // Import Layout attribute
use Illuminate\Support\Facades\Log; // Import Log facade

#[Layout('layouts.app')]
class ChatDashboard extends Component
{
    /**
     * The currently selected conversation ID, synced with the URL.
     * @var int|null
     */
    #[Url(as: 'id')]
    public ?int $selectedConversationId = null;

    /**
     * Get the collection of conversations for the authenticated user.
     */
    #[\Livewire\Attributes\Computed]
    public function conversations()
    {
        $user = Auth::user();
        if (!$user) {
            return new Collection();
        }

        return app(ChatService::class)->getConversations($user);
    }

    /**
     * Get the currently selected conversation model.
     */
    #[\Livewire\Attributes\Computed]
    public function selectedConversation()
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        return Conversation::with(['product', 'userOne', 'userTwo'])
            ->where(function ($q) {
                $q->where('user_one_id', Auth::id())
                    ->orWhere('user_two_id', Auth::id());
            })
            ->find($this->selectedConversationId);
    }


    /**
     * Mount the component, fetch conversations, and select the initial one.
     *
     * @param ChatService $chatService
     * @return void
     */
    public function mount(ChatService $chatService): void
    {
        // If no ID in URL, default to the first one
        if (!$this->selectedConversationId && $this->conversations->isNotEmpty()) {
            $this->selectedConversationId = $this->conversations->first()->id;
        }

        // Initial mark as read if selected
        if ($this->selectedConversation) {
            $chatService->markAsRead($this->selectedConversation, Auth::user());
        }
    }

    /**
     * Selects a conversation and updates the selectedConversation property.
     * Dispatches an event for the ChatWindow component.
     *
     * @param int $conversationId
     * @return void
     */
    public function selectConversation(int $conversationId): void
    {
        // If already selected, do nothing to avoid redundant state updates
        if ($this->selectedConversationId === $conversationId) {
            return;
        }

        $this->selectedConversationId = $conversationId;

        if ($this->selectedConversation) {
            app(ChatService::class)->markAsRead($this->selectedConversation, Auth::user());
        }
    }

    #[\Livewire\Attributes\On('refresh-dashboard')]
    public function refreshDashboard(): void
    {
        // Re-renders the component to update conversation list (e.g. last message, unread status)
        Log::debug("ChatDashboard: refreshDashboard triggered manually.");
    }

    public function getListeners()
    {
        $authId = Auth::id();
        return [
            "echo-private:App.Models.User.{$authId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'handleNotification',
        ];
    }

    public function handleNotification($data): void
    {
        Log::debug("ChatDashboard: Notification received via Echo", ['data' => $data]);

        // If the notification has a conversation ID, we can check if it's the current one
        $convoId = null;
        if (isset($data['url']) && preg_match('/id=(\d+)/', $data['url'], $matches)) {
            $convoId = (int) $matches[1];
        }

        // If it's for another conversation, refresh the dashboard to update list/counts
        // If it's for the current one, the ChatWindow is already refreshing itself.
        // We still refresh the Dashboard to update the sidebar preview, but we do it safely.
        $this->refreshDashboard();
    }

    /**
     * Render the component's view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Renders the view: Modules/Chat/Resources/views/livewire/chat-dashboard.blade.php
        // Uses the main backend layout defined in your application.
        return view('chat::livewire.chat-dashboard');
    }
}
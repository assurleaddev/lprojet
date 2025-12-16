<?php

namespace Modules\Chat\Livewire;

use Livewire\Component;
use Modules\Chat\Services\ChatService; // Ensure correct import for your service
use Modules\Chat\Models\Conversation; // Ensure correct import for your Conversation model
use Illuminate\Database\Eloquent\Collection; // Make sure Eloquent Collection is imported
use Illuminate\Support\Facades\Auth; // Import Auth facade

use Livewire\Attributes\Url; // Import Url attribute
use Livewire\Attributes\Layout; // Import Layout attribute

#[Layout('layouts.app')]
class ChatDashboard extends Component
{
    /**
     * The collection of conversations for the authenticated user.
     * @var Collection
     */
    public Collection $conversations;

    /**
     * The currently selected conversation model.
     * @var Conversation|null
     */
    public ?Conversation $selectedConversation = null;

    /**
     * The conversation ID passed via the URL query string (for deep-linking).
     * Set by Livewire automatically if present in the route parameters.
     * @var int|null
     */
    #[Url(as: 'id')]
    public ?int $queryConversationId = null; // Renamed to match the route parameter

    /**
     * Mount the component, fetch conversations, and select the initial one.
     *
     * @param ChatService $chatService
     * @return void
     */
    public function mount(ChatService $chatService): void
    {
        // Get the authenticated user
        $user = Auth::user();
        if (!$user) {
            $this->conversations = new Collection();
            return;
        }

        $this->conversations = $chatService->getConversations($user);
        // $this->queryConversationId is automatically set by Livewire from query string
        $this->initializeSelectedConversation();
    }

    /**
     * Sets the initial selected conversation based on the URL parameter or the latest conversation.
     *
     * @return void
     */
    protected function initializeSelectedConversation(): void
    {
        if ($this->queryConversationId) {
            $conversation = $this->conversations->firstWhere('id', $this->queryConversationId);
            if ($conversation) {
                $this->selectConversation($conversation->id);
                return;
            }
        }

        if ($this->selectedConversation === null && $this->conversations->isNotEmpty()) {
            $this->selectConversation($this->conversations->first()->id);
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
        $selected = $this->conversations->firstWhere('id', $conversationId);

        if ($selected) {
            $this->selectedConversation = $selected;
            app(ChatService::class)->markAsRead($this->selectedConversation, Auth::user());
        }
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
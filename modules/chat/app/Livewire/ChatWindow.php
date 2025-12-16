<?php

namespace Modules\Chat\Livewire;

use Livewire\Component;
use Modules\Chat\Services\ChatService; // Ensure correct import for your service
use Modules\Chat\Models\Conversation; // Ensure correct import for your Conversation model
use Modules\Chat\Models\Offer; // Import Offer model
use Modules\Chat\Enums\OfferStatus; // Import OfferStatus enum
use Illuminate\Database\Eloquent\Collection; // Make sure Eloquent Collection is imported
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Log; // Import Log facade
use Livewire\Attributes\Layout; // Import Layout attribute if needed (usually handled by parent)
use Livewire\Attributes\On; // Import the On attribute for Livewire events

class ChatWindow extends Component
{
    /**
     * The ID of the current conversation being viewed.
     * Passed from the parent ChatDashboard component.
     * @var int
     */
    public int $conversationId;

    /**
     * The loaded Conversation model instance.
     * @var Conversation|null
     */
    public ?Conversation $conversation = null;

    /**
     * Array holding the messages for the current conversation.
     * @var array
     */
    public array $messages = [];

    /**
     * Bound to the message input field.
     * @var string
     */
    public string $messageBody = '';

    // Properties for Rejection Modal
    public bool $showRejectionModal = false;
    public ?int $offerToRejectId = null;
    public string $rejectionReason = '';

    /**
     * Mount the component, load the initial conversation and messages.
     *
     * @param int $conversationId
     * @param ChatService $chatService
     * @return void
     */
    public function mount(int $conversationId, ChatService $chatService): void
    {
        $this->conversationId = $conversationId;
        // Load conversation immediately
        $this->loadConversation($chatService);
    }

    /**
     * Loads the conversation details and its messages.
     * Marks messages as read upon loading.
     *
     * @param ChatService $chatService
     * @return void
     */
    /**
     * Loads the conversation details and its messages.
     * Marks messages as read upon loading.
     *
     * @param ChatService $chatService
     * @return void
     */
    protected function loadConversation(ChatService $chatService): void
    {
        $user = Auth::user();
        if (!$user) {
            Log::error("ChatWindow: User not authenticated while trying to load conversation {$this->conversationId}");
            $this->messages = [];
            $this->conversation = null; // Ensure conversation is null if user isn't auth'd
            return;
        }

        // Find the conversation, ensuring the user has access and eager load necessary relations
        $this->conversation = Conversation::where('id', $this->conversationId)
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            // Eager load messages, their sender, and any associated offer with its product
            // Product model should handle loading its media automatically or define the accessor
            ->with([
                'messages' => function ($query) {
                    $query->with(['user', 'offer.product']); // Eager load product on offer
                },
                'product', // Load conversation's primary product
                'userOne', // Load user one
                'userTwo'  // Load user two
            ])
            ->first(); // Use first() and check

        if ($this->conversation) {
            // Prepare messages array, ensuring offer/product data is structured correctly
            $this->messages = $this->conversation->messages->map(function ($message) {
                $messageArray = $message->toArray(); // Convert message to array

                // If there's an offer, ensure product and its featured image URL are included
                if ($message->offer && $message->offer->product) {
                    // Use the user's specific method here to get the image URL
                    // Make sure the Product model instance is available via $message->offer->product
                    $messageArray['offer']['product']['featured_image_url'] = $message->offer->product->getFeaturedImageUrl('preview');

                    // Manually add name/price if not included by default in toArray or relationships
                    if (!isset($messageArray['offer']['product']['name'])) {
                        $messageArray['offer']['product']['name'] = $message->offer->product->name;
                    }
                    if (!isset($messageArray['offer']['product']['price'])) {
                        $messageArray['offer']['product']['price'] = $message->offer->product->price;
                    }

                } else {
                    // Ensure nested structure exists even if there's no offer, avoids errors in Blade
                    if (!isset($messageArray['offer']))
                        $messageArray['offer'] = null;
                    if ($messageArray['offer'] && !isset($messageArray['offer']['product']))
                        $messageArray['offer']['product'] = null;
                    if ($messageArray['offer'] && $messageArray['offer']['product'] && !isset($messageArray['offer']['product']['featured_image_url']))
                        $messageArray['offer']['product']['featured_image_url'] = null;
                }
                return $messageArray;
            })->keyBy(function ($item) { // Key by offer or message ID after mapping
                // Use unique keys: prefix with type and ensure message ID exists
                $msgId = $item['id'] ?? uniqid('msg_', true);
                $offerId = $item['offer_id'] ?? null;
                return $offerId ? 'offer_' . $offerId : 'msg_' . $msgId;
            })->toArray();

            // Mark messages as read
            $chatService->markAsRead($this->conversation, $user);
            Log::debug("ChatWindow: Successfully loaded conversation {$this->conversationId} with " . count($this->messages) . " messages.");
        } else {
            // Handle case where conversation isn't found or user doesn't have access
            $this->messages = [];
            Log::warning("ChatWindow: Attempted to load invalid or inaccessible conversation ID {$this->conversationId} for User {$user->id}");
            // Optionally redirect or show an error state in the view
        }
    }

    /**
     * This method is triggered by the Alpine.js bridge when a 'new-message'
     * event is received via Pusher/Echo for this conversation.
     * It reloads the conversation messages.
     *
     * @param ChatService $chatService
     * @return void
     */
    #[On('refresh-chat')]
    public function refreshMessages(ChatService $chatService): void
    {
        Log::debug("ChatWindow: refreshMessages triggered for Conversation {$this->conversationId}"); // Debug log
        $this->loadConversation($chatService); // Reloads all messages and marks as read

        // Dispatch event back to Alpine to trigger scroll down
        $this->dispatch('message-received', conversationId: $this->conversationId);
    }

    /**
     * Sends a new message using the ChatService.
     * Adds the message locally and dispatches event for scrolling.
     *
     * @param ChatService $chatService
     * @return void
     */
    public function sendMessage(ChatService $chatService): void
    {
        $this->validate(['messageBody' => 'required|string|max:2000']);

        if (!$this->conversation) {
            Log::error("ChatWindow: Attempted to send message on unloaded conversation ID {$this->conversationId}");
            $this->addError('messageBody', 'Cannot send message. Conversation not loaded.');
            return;
        }
        $user = Auth::user();
        if (!$user)
            return;

        $newMessage = $chatService->sendMessage(
            $this->conversation,
            $user,
            $this->messageBody
        );

        Log::debug("ChatWindow: Message sent successfully by User {$user->id} in Conversation {$this->conversationId}");

        // Add the newly sent message immediately to the local state
        $key = 'msg_' . $newMessage->id; // Use msg_ prefix for non-offer messages
        $this->messages[$key] = [
            'id' => $newMessage->id,
            'conversation_id' => $this->conversationId,
            'user_id' => $user->id,
            'body' => $newMessage->body,
            'read_at' => null,
            'created_at' => $newMessage->created_at->toISOString(),
            'updated_at' => $newMessage->updated_at->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name, // Adjust attribute if needed
            ],
            'created_at_human' => $newMessage->created_at->diffForHumans(),
            'type' => 'text', // Explicitly set type
            'offer_id' => null, // No offer linked
            'offer' => null, // No offer data
        ];

        $this->reset('messageBody');
        $this->dispatch('message-sent', conversationId: $this->conversationId);
    }

    // Method to Accept an Offer
    public function acceptOffer(int $offerId, ChatService $chatService): void
    {
        $user = Auth::user();
        $offer = Offer::find($offerId);

        if (!$offer) {
            $this->dispatch('toast', message: 'Offer not found.', type: 'error');
            return;
        }

        // Case 1: Seller accepting a Pending offer (Standard flow)
        if ($offer->status === OfferStatus::Pending && $offer->seller_id === $user->id) {
            $offer->status = OfferStatus::Accepted;
            $offer->responded_at = now();
            $offer->save();

            Log::info("ChatWindow: Offer {$offerId} accepted by Seller " . $user->id);

            $chatService->sendOfferResponseMessage($offer->conversation, $user, $offer, true);

            $this->loadConversation($chatService);
            $this->dispatch('toast', message: 'Offer accepted!', type: 'success');
            return;
        }

        // Case 2: Buyer accepting a Counter Offer (AwaitingBuyer)
        if ($offer->status === OfferStatus::AwaitingBuyer && $offer->buyer_id === $user->id) {
            $offer->status = OfferStatus::Accepted;
            $offer->responded_at = now();
            $offer->save();

            Log::info("ChatWindow: Counter Offer {$offerId} accepted by Buyer " . $user->id);

            // Send message confirming acceptance
            $chatService->sendOfferResponseMessage($offer->conversation, $user, $offer, true, 'Counter offer accepted');

            // Redirect to checkout
            $this->redirect(route('checkout.offer', ['offer' => $offer->id]), navigate: true);
            return;
        }

        // If neither case matched
        Log::warning("ChatWindow: Invalid accept attempt for offer {$offerId} by User " . $user->id);
        $this->dispatch('toast', message: 'Unauthorized action or invalid offer status.', type: 'error');
    }

    // Method to show the rejection reason modal
    public function promptRejectOffer(int $offerId): void
    {
        $offerExists = Offer::where('id', $offerId)
            ->where('seller_id', Auth::id())
            ->where('status', OfferStatus::Pending)
            ->exists();

        if ($offerExists) {
            $this->offerToRejectId = $offerId;
            $this->reset('rejectionReason');
            $this->showRejectionModal = true;
        } else {
            Log::warning("ChatWindow: Invalid or unauthorized attempt to prompt rejection for offer {$offerId} by User " . Auth::id());
            $this->dispatch('toast', message: 'Offer not found or already actioned.', type: 'error'); // Updated dispatch syntax
        }
    }

    // Method to submit the rejection with reason
    public function rejectOffer(ChatService $chatService): void
    {
        if ($this->offerToRejectId === null)
            return;

        $validated = $this->validate([
            'rejectionReason' => 'required|string|min:10|max:500',
        ]);

        $offer = Offer::where('id', $this->offerToRejectId)
            ->where('seller_id', Auth::id())
            ->where('status', OfferStatus::Pending)
            ->first();

        if (!$offer) {
            Log::warning("ChatWindow: Offer {$this->offerToRejectId} not found or invalid state during rejection by User " . Auth::id());
            $this->closeRejectionModal();
            $this->dispatch('toast', message: 'Offer not found or already actioned.', type: 'error'); // Updated dispatch syntax
            return;
        }

        $offer->status = OfferStatus::Rejected;
        $offer->rejection_reason = $validated['rejectionReason'];
        $offer->responded_at = now();
        $offer->save();

        Log::info("ChatWindow: Offer {$this->offerToRejectId} rejected by User " . Auth::id() . " Reason: " . $validated['rejectionReason']);

        $chatService->sendOfferResponseMessage($offer->conversation, Auth::user(), $offer, false, $validated['rejectionReason']);

        $this->closeRejectionModal();
        $this->loadConversation($chatService); // Re-fetch to update $this->messages
        $this->dispatch('toast', message: 'Offer rejected.', type: 'info'); // Updated dispatch syntax
    }

    public function closeRejectionModal(): void
    {
        $this->showRejectionModal = false;
        $this->offerToRejectId = null;
        $this->reset('rejectionReason');
        $this->resetValidation('rejectionReason');
    }

    public function markAsShipped(ChatService $chatService)
    {
        // Find the order associated with this conversation's product
        // Assuming one active order per product/conversation context
        $order = \App\Models\Order::where('product_id', $this->conversation->product_id)
            ->where('vendor_id', Auth::id())
            ->where('status', 'processing') // Only look for processing orders
            ->latest()
            ->first();

        if (!$order) {
            $this->dispatch('toast', message: 'No processing order found to ship.', type: 'error');
            return;
        }

        $order->update(['status' => 'shipped']);

        // Send structured message
        $chatService->sendItemShippedMessage($this->conversation, Auth::user(), $order);

        $this->dispatch('toast', message: 'Order marked as shipped.', type: 'success');
        $this->loadConversation($chatService);
    }

    // Properties for Review Modal
    public bool $showReviewModal = false;
    public int $reviewRating = 0;
    public string $reviewText = '';
    public ?int $orderToReviewId = null;

    public function markAsReceived(int $orderId)
    {
        if ($orderId === 0) {
            // Try to find the order if ID is not passed (e.g. from chat view)
            $order = \App\Models\Order::where('product_id', $this->conversation->product_id)
                ->where('user_id', Auth::id()) // Current user must be the buyer
                ->where('status', 'shipped')
                ->latest()
                ->first();
        } else {
            $order = \App\Models\Order::find($orderId);
        }

        if (!$order || $order->user_id !== Auth::id()) {
            $this->dispatch('toast', message: 'Unauthorized action or order not found.', type: 'error');
            return;
        }

        if ($order->status !== 'shipped') {
            $this->dispatch('toast', message: 'Order cannot be marked as received yet.', type: 'error');
            return;
        }

        // Open Review Modal instead of completing immediately
        $this->orderToReviewId = $order->id;
        $this->reset(['reviewRating', 'reviewText']);
        $this->showReviewModal = true;
    }

    public function submitReview(ChatService $chatService)
    {
        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewText' => 'nullable|string|max:1000',
        ]);

        if (!$this->orderToReviewId)
            return;

        $order = \App\Models\Order::find($this->orderToReviewId);
        if (!$order)
            return;

        // 1. Create Review
        $reviewContent = empty(trim($this->reviewText)) ? "Auto generated review by user" : $this->reviewText;

        \App\Models\Review::create([
            'rating' => $this->reviewRating,
            'review' => $reviewContent,
            'model_id' => $order->vendor_id, // Reviewing the Seller
            'model_type' => \App\Models\User::class,
            'author_id' => Auth::id(), // Buyer
            'author_type' => \App\Models\User::class,
            // 'order_id' => $order->id, // If we added order_id to reviews table, which we didn't in the migration I saw. 
            // The migration had morphs for model and author. 
            // If we want to link to order, we'd need a column. For now, let's skip strict order linking in DB 
            // or put it in title/meta if needed. But requirement didn't strictly say "link in DB", just "added to buyer reviews".
        ]);

        // 2. Release Funds & Complete Order
        try {
            $walletService = app(\Modules\Wallet\Services\WalletService::class);
            $walletService->releasePendingFunds($order->vendor, $order->amount, 'Order #' . $order->id);

            $order->update(['status' => 'completed']); // Use completed instead of delivered for final state

            // 3. Send Message
            $chatService->sendOrderCompletedMessage($this->conversation, Auth::user(), $order);

            $this->dispatch('toast', message: 'Order completed and review submitted!', type: 'success');
            $this->closeReviewModal();
            $this->loadConversation($chatService);

        } catch (\Exception $e) {
            Log::error("ChatWindow: Error completing order {$order->id}: " . $e->getMessage());
            $this->dispatch('toast', message: 'Error completing order: ' . $e->getMessage(), type: 'error');
        }
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
        $this->orderToReviewId = null;
        $this->reset(['reviewRating', 'reviewText']);
    }

    /**
     * Triggers the Counter Offer Modal by dispatching an event.
     * This acts as a proxy to ensure the event is dispatched from the backend.
     *
     * @param int $productId
     * @param int $targetBuyerId
     * @return void
     */
    public function triggerCounterOffer(int $productId, int $targetBuyerId): void
    {
        Log::info("ChatWindow: Triggering counter offer modal for Product {$productId} and Buyer {$targetBuyerId}");
        $this->dispatch('open-counter-offer-modal', productId: $productId, targetBuyerId: $targetBuyerId);
    }

    /**
     * Render the component's view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Renders the view: Modules/Chat/Resources/views/livewire/chat-window.blade.php
        return view('chat::livewire.chat-window');
    }
}
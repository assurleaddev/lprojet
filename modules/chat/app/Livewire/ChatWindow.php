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
     * Get the loaded Conversation model instance via computed property.
     */
    #[\Livewire\Attributes\Computed]
    public function conversation()
    {
        $user = Auth::user();
        if (!$user)
            return null;

        return Conversation::where('id', $this->conversationId)
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->with([
                'messages' => function ($query) {
                    $query->with(['user', 'offer.product', 'attachments']);
                },
                'product',
                'userOne',
                'userTwo'
            ])
            ->first();
    }

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

    // File Uploads
    use \Livewire\WithFileUploads;
    public $attachments = []; // For the file input (multiple)

    public bool $showRejectionModal = false;
    public ?int $offerToRejectId = null;
    public string $rejectionReason = '';

    // Advanced Features Properties
    public bool $isOtherUserOnline = false;
    public array $typingUsers = [];

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
        // The conversation property is computed, so we just need to load initial messages
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
            return;
        }

        // Access computed property
        $conversation = $this->conversation;

        if ($conversation) {
            // Mark messages as read and delivered BEFORE loading to ensure correct status in the array
            $chatService->markAsRead($conversation, $user);
            $chatService->markAsDelivered($conversation, $user);

            // Re-fetch conversation to get updated timestamps in messages
            $conversation->refresh();

            // Prepare messages array, ensuring offer/product data is structured correctly
            $this->messages = $conversation->messages->map(function ($message) {
                $messageArray = $message->toArray(); // Convert message to array

                if ($message->offer && $message->offer->product) {
                    $messageArray['offer']['product']['featured_image_url'] = $message->offer->product->getFeaturedImageUrl('preview');
                    if (!isset($messageArray['offer']['product']['name'])) {
                        $messageArray['offer']['product']['name'] = $message->offer->product->name;
                    }
                    if (!isset($messageArray['offer']['product']['price'])) {
                        $messageArray['offer']['product']['price'] = $message->offer->product->price;
                    }
                } else {
                    if (!isset($messageArray['offer']))
                        $messageArray['offer'] = null;
                }

                // Ensure attachments are included if they exist
                $messageArray['attachments'] = $message->attachments->toArray();

                return $messageArray;
            })->keyBy(function ($item) {
                $msgId = $item['id'] ?? uniqid('msg_', true);
                $offerId = $item['offer_id'] ?? null;
                return $offerId ? 'offer_' . $offerId : 'msg_' . $msgId;
            })->toArray();

            Log::debug("ChatWindow: Successfully loaded conversation {$this->conversationId} with " . count($this->messages) . " messages.");
        } else {
            $this->messages = [];
            Log::warning("ChatWindow: Attempted to load invalid or inaccessible conversation ID {$this->conversationId} for User {$user->id}");
        }
    }

    public function getListeners()
    {
        return [
            // Echo listeners are handled in Blade (x-init) for better Presence stability
            'refresh-chat' => 'refreshMessages',
            'refresh-read-status' => 'refreshReadStatus',
        ];
    }

    public function refreshMessages(?ChatService $chatService = null): void
    {
        $chatService = $chatService ?? app(ChatService::class);
        Log::debug("ChatWindow: refreshMessages triggered for Conversation {$this->conversationId}");
        $this->loadConversation($chatService);
        $this->dispatch('message-received', conversationId: $this->conversationId);

        // Notify Parent Dashboard to refresh sidebar (unread dots, last message preview)
        $this->dispatch('refresh-dashboard');
    }

    public function refreshReadStatus(): void
    {
        $this->loadConversation(app(ChatService::class));
    }

    public function withdrawOffer(int $offerId, ChatService $chatService): void
    {
        $offer = Offer::find($offerId);
        if (!$offer)
            return;

        try {
            $chatService->withdrawOffer($offer, Auth::user());
            $this->loadConversation($chatService);
            $this->dispatch('toast', message: 'Offer withdrawn.', type: 'info');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function reserveProduct(ChatService $chatService): void
    {
        if (!$this->conversation || !$this->conversation->product)
            return;

        try {
            $otherUser = $this->conversation->getOtherUser(Auth::user());
            $chatService->reserveProduct($this->conversation->product, Auth::user(), $otherUser);
            $this->loadConversation($chatService);
            $this->dispatch('toast', message: 'Product reserved!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function unreserveProduct(ChatService $chatService): void
    {
        if (!$this->conversation || !$this->conversation->product)
            return;

        try {
            $chatService->unreserveProduct($this->conversation->product, Auth::user());
            $this->loadConversation($chatService);
            $this->dispatch('toast', message: 'Product is now available.', type: 'info');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
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
        $this->validate([
            'messageBody' => 'nullable|string|max:2000',
            'attachments.*' => 'file|max:10240', // Max 10MB per file
        ]);

        if (empty($this->messageBody) && empty($this->attachments)) {
            $this->addError('messageBody', 'Message or file is required.');
            return;
        }

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
            $this->messageBody,
            $this->attachments // Pass the uploaded files array
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
            'attachments' => $newMessage->attachments->toArray(), // Load new attachments
        ];

        $this->reset(['messageBody', 'attachments']); // Reset attachments
        $this->dispatch('message-sent', conversationId: $this->conversationId);
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments); // Re-index array
        }
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
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('seller_id', Auth::id())
                        ->where('status', OfferStatus::Pending);
                })->orWhere(function ($q) {
                    $q->where('buyer_id', Auth::id())
                        ->where('status', OfferStatus::AwaitingBuyer);
                });
            })
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
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('seller_id', Auth::id())
                        ->where('status', OfferStatus::Pending);
                })->orWhere(function ($q) {
                    $q->where('buyer_id', Auth::id())
                        ->where('status', OfferStatus::AwaitingBuyer);
                });
            })
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

    // Properties for Reception Confirmation Modal
    public bool $showReceptionConfirmationModal = false;

    public function markAsReceived(int $orderId)
    {
        if ($orderId === 0) {
            // Try to find the order if ID is not passed (e.g. from chat view)
            $order = \App\Models\Order::where('product_id', $this->conversation->product_id)
                ->where('user_id', Auth::id())
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
            // If already delivered but not reviewed?
            if ($order->status === 'delivered') {
                $this->openReviewModal($order->id);
                return;
            }
            $this->dispatch('toast', message: 'Order cannot be marked as received yet.', type: 'error');
            return;
        }

        // Open Confirmation Modal
        $this->orderToReviewId = $order->id; // Using temporary storage
        $this->showReceptionConfirmationModal = true;
    }

    public function confirmReception()
    {
        if (!$this->orderToReviewId)
            return;

        $order = \App\Models\Order::find($this->orderToReviewId);
        if (!$order || $order->user_id !== Auth::id())
            return;

        // Update Status to Delivered
        $order->update([
            'status' => 'delivered',
            'received_at' => now(),
        ]);

        $this->showReceptionConfirmationModal = false;

        // Immediately open review modal
        $this->openReviewModal($order->id);

        $this->dispatch('toast', message: 'Item marked as received. Please leave a review.', type: 'success');
        $this->loadConversation(app(ChatService::class));
    }

    public function openReviewModal($orderId)
    {
        $this->orderToReviewId = $orderId;
        $this->reset(['reviewRating', 'reviewText']);
        $this->showReviewModal = true;
    }

    public function submitReview(ChatService $chatService)
    {
        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewText' => 'required|string|min:5|max:1000', // Required per request
        ]);

        if (!$this->orderToReviewId)
            return;

        $order = \App\Models\Order::find($this->orderToReviewId);
        if (!$order)
            return;

        // Ensure order is delivered before reviewing/completing
        if ($order->status !== 'delivered' && $order->status !== 'completed') {
            // Handle edge case if status wasn't updated correctly, or force update?
            // Assuming normal flow: shipped -> delivered -> completed
            // If manual override needed, we might allow it but standard flow requires delivered.
            if ($order->status === 'shipped') {
                // Auto-move to delivered if skipping confirmation? No, enforce flow.
                $this->dispatch('toast', message: 'Please confirm item reception first.', type: 'error');
                return;
            }
        }

        // Check if already reviewed (prevent duplicates)
        $existingReview = \App\Models\Review::where('author_id', Auth::id())
            ->where('model_id', $order->vendor_id)
            ->where('model_type', \App\Models\User::class)
            ->where('created_at', '>', $order->created_at) // Simple heuristic or need exact order link
            // Ideally should check against order ID if we linked it, but for now we enforce via status check
            ->exists();

        if ($order->status === 'completed') {
            $this->dispatch('toast', message: 'Order already completed and reviewed.', type: 'info');
            $this->closeReviewModal();
            return;
        }

        // 1. Create Review
        \App\Models\Review::create([
            'rating' => $this->reviewRating,
            'review' => $this->reviewText,
            'model_id' => $order->vendor_id, // Reviewing the Seller
            'model_type' => \App\Models\User::class,
            'author_id' => Auth::id(), // Buyer
            'author_type' => \App\Models\User::class,
        ]);

        // 2. Release Funds & Complete Order
        try {
            $walletService = app(\Modules\Wallet\Services\WalletService::class);
            $walletService->releasePendingFunds($order->vendor, $order->amount, 'Order #' . $order->id);

            $order->update(['status' => 'completed']);

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
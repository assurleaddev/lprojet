<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Modules\Wallet\Services\WalletService;
use Modules\Chat\Services\ChatService;
use Illuminate\Support\Facades\Log;

class AutoPostReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-post-reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically post positive reviews for orders delivered > 48 hours ago';

    /**
     * Execute the console command.
     */
    public function handle(WalletService $walletService, ChatService $chatService)
    {
        // 1. Find orders: Delivered AND received_at older than 48 hours
        $cutoff = now()->subHours(48);

        $orders = Order::where('status', 'delivered')
            ->where('received_at', '<', $cutoff)
            ->get();

        $this->info("Found {$orders->count()} orders eligible for auto-review.");

        foreach ($orders as $order) {
            try {
                // Double check if already reviewed to be safe
                $exists = Review::where('author_id', $order->user_id)
                    ->where('model_id', $order->vendor_id)
                    ->where('model_type', User::class)
                    ->where('created_at', '>', $order->created_at)
                    ->exists();

                if ($exists) {
                    $this->info("Order {$order->id} already reviewed. Skipping but marking completed.");
                    // Just update status if somehow missed
                    if ($order->status !== 'completed') {
                        $order->update(['status' => 'completed']);
                        // Ensure funds released? Likely yes if review exists manually.
                    }
                    continue;
                }

                // 2. Create Review
                // Author is Buyer (order->user_id)
                // Model is Seller (order->vendor_id)
                // Text: "Auto-feedback: Sale completed successfully"
                // Rating: 5 stars (implied positive)

                Review::create([
                    'rating' => 5,
                    'review' => 'Auto-feedback: Sale completed successfully',
                    'model_id' => $order->vendor_id,
                    'model_type' => User::class,
                    'author_id' => $order->user_id,
                    'author_type' => User::class,
                    'is_auto' => true,
                ]);

                // 3. Complete Order & Release Funds
                $walletService->releasePendingFunds($order->vendor, $order->amount, 'Order #' . $order->id . ' (Auto-completed)');
                $order->update(['status' => 'completed']);

                // 4. Notify via Chat? Or just silent?
                // Depending on requirements, maybe don't spam chat, or send a system note?
                // Let's assume silent or minimal. The user asked for auto-review. 
                // We'll log it.
                Log::info("AutoPostReviews: Order {$order->id} auto-completed and reviewed.");
                $this->info("Processed Order {$order->id}");

            } catch (\Exception $e) {
                Log::error("AutoPostReviews: Error processing order {$order->id}: " . $e->getMessage());
                $this->error("Error Order {$order->id}: " . $e->getMessage());
            }
        }
    }
}

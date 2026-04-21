<?php

namespace App\Observers;

use Digikraaft\ReviewRating\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        // Check if the review is for a user (Vendor)
        if ($review->model_type === 'App\Models\User') {
            $vendor = \App\Models\User::find($review->model_id);
            if ($vendor) {
                try {
                    $vendor->notify(new \App\Notifications\ReviewReceivedNotification($review->author, $review->rating));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('ReviewReceivedNotification failed: ' . $e->getMessage());
                }
            }
        } elseif ($review->model_type === 'App\Models\Product') {
            $product = \App\Models\Product::find($review->model_id);
            if ($product && $product->vendor && $review->author instanceof \App\Models\User) {
                try {
                    $product->vendor->notify(new \App\Notifications\ReviewReceivedNotification($review->author, $review->rating));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('ReviewReceivedNotification failed: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}

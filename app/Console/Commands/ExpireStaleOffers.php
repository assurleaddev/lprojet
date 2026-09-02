<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Chat\Enums\OfferStatus;
use Modules\Chat\Models\Offer;

class ExpireStaleOffers extends Command
{
    protected $signature = 'offers:expire';

    protected $description = 'Mark pending / awaiting-buyer offers past their expires_at as expired';

    public function handle(): int
    {
        $expired = Offer::whereIn('status', [OfferStatus::Pending, OfferStatus::AwaitingBuyer])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update([
                'status' => OfferStatus::Expired,
                'responded_at' => now(),
            ]);

        $this->info("Expired {$expired} stale offer(s).");

        return self::SUCCESS;
    }
}

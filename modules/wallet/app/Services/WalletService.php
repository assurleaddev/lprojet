<?php

namespace Modules\Wallet\Services;

use App\Models\User;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\Transaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function getWallet(User $user): Wallet
    {
        return $user->wallet ?? $user->wallet()->create(['balance' => 0]);
    }

    public function getBalance(User $user): float
    {
        return (float) $this->getWallet($user)->balance;
    }

    public function credit(User $user, float $amount, string $type, ?string $description = null, ?string $referenceId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceId) {
            $wallet = $this->getWallet($user);
            $wallet->balance += $amount;
            $wallet->save();

            return $wallet->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);
        });
    }

    public function debit(User $user, float $amount, string $type, ?string $description = null, ?string $referenceId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceId) {
            $wallet = $this->getWallet($user);

            if ($wallet->balance < $amount) {
                throw new \Exception("Insufficient balance.");
            }

            $wallet->balance -= $amount;
            $wallet->save();

            return $wallet->transactions()->create([
                'type' => $type,
                'amount' => -$amount,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);
        });
    }

    public function transfer(User $from, User $to, float $amount, ?string $description = null, ?string $referenceId = null)
    {
        return DB::transaction(function () use ($from, $to, $amount, $description, $referenceId) {
            $this->debit($from, $amount, 'transfer_out', "Transfer to {$to->name}: $description", $referenceId);
            $this->credit($to, $amount, 'transfer_in', "Transfer from {$from->name}: $description", $referenceId);
        });
    }

    public function payWithWallet(User $buyer, User $vendor, float $amount, string $referenceId)
    {
        return DB::transaction(function () use ($buyer, $vendor, $amount, $referenceId) {
            $this->debit($buyer, $amount, 'purchase', "Purchase from {$vendor->name}", $referenceId);
            $this->credit($vendor, $amount, 'sale', "Sale to {$buyer->name}", $referenceId);
        });
    }

    public function payToEscrow(User $buyer, User $vendor, float $totalAmount, float $itemPrice, string $referenceId)
    {
        return DB::transaction(function () use ($buyer, $vendor, $totalAmount, $itemPrice, $referenceId) {
            // Debit the buyer for the FULL amount (Item + Shipping + Fees)
            $this->debit($buyer, $totalAmount, 'purchase', "Purchase from {$vendor->name} (Escrow)", $referenceId);

            // Credit the vendor's PENDING balance with the Item Price only
            // Fees and shipping might go to platform or be held elsewhere, but for now we focus on vendor payout.
            $vendorWallet = $this->getWallet($vendor);
            $vendorWallet->pending_balance += $itemPrice;
            $vendorWallet->save();

            // Log the pending transaction? Or just rely on the wallet state?
            // Let's create a transaction record for visibility, but maybe with a specific status if we had one.
            // For now, we just update the pending balance column.
        });
    }

    public function releasePendingFunds(User $vendor, float $amount, string $referenceId)
    {
        return DB::transaction(function () use ($vendor, $amount, $referenceId) {
            $wallet = $this->getWallet($vendor);

            if ($wallet->pending_balance < $amount) {
                // Should not happen in normal flow, but good to check
                throw new \Exception("Insufficient pending balance.");
            }

            $wallet->pending_balance -= $amount;
            $wallet->balance += $amount; // Move to available balance
            $wallet->save();

            // Create a transaction record to show the funds are now available
            return $wallet->transactions()->create([
                'type' => 'sale_released',
                'amount' => $amount,
                'description' => "Funds released for $referenceId",
                'reference_id' => $referenceId,
            ]);
        });
    }
}

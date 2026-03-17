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
        return $this->creditWallet($this->getWallet($user), $amount, $type, $description, $referenceId);
    }

    public function debit(User $user, float $amount, string $type, ?string $description = null, ?string $referenceId = null): Transaction
    {
        return $this->debitWallet($this->getWallet($user), $amount, $type, $description, $referenceId);
    }

    public function creditWallet(Wallet $wallet, float $amount, string $type, ?string $description = null, ?string $referenceId = null): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $type, $description, $referenceId) {
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

    public function debitWallet(Wallet $wallet, float $amount, string $type, ?string $description = null, ?string $referenceId = null): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $type, $description, $referenceId) {
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

    public function getPlatformWallet(): Wallet
    {
        return Wallet::firstOrCreate(
            ['name' => 'platform'],
            ['user_id' => null, 'balance' => 0, 'pending_balance' => 0]
        );
    }

    public function payToEscrow(User $buyer, User $vendor, float $totalAmount, float $vendorNetPayout, string $referenceId, float $platformRevenue = 0)
    {
        return DB::transaction(function () use ($buyer, $vendor, $totalAmount, $vendorNetPayout, $referenceId, $platformRevenue) {
            // Debit the buyer for the FULL amount (Item + Shipping + Fees)
            $this->debit($buyer, $totalAmount, 'purchase', "Purchase from {$vendor->name} (Escrow)", $referenceId);

            // Credit the vendor's PENDING balance with the Net Payout only
            $vendorWallet = $this->getWallet($vendor);
            $vendorWallet->pending_balance += $vendorNetPayout;
            $vendorWallet->save();

            // Credit the platform wallet with Fees + Commission
            if ($platformRevenue > 0) {
                $platformWallet = $this->getPlatformWallet();
                $this->creditWallet($platformWallet, $platformRevenue, 'platform_fee', "Fees from $referenceId", $referenceId);
            }
        });
    }

    public function refundOrder(\App\Models\Order $order)
    {
        return DB::transaction(function () use ($order) {
            $buyer = $order->user;
            $vendor = $order->vendor;

            // 1. Calculate Refund Amount
            $refundBaseAmount = $order->total_amount;
            $refundCommissionPercentage = config('settings.refund_commission_percentage', 0);
            $refundDeduction = $refundBaseAmount * ($refundCommissionPercentage / 100);
            $refundAmount = $refundBaseAmount - $refundDeduction;

            // 2. Reclaim funds from Vendor's PENDING balance
            $commissionAmount = $order->platform_commission ?? 0;
            $vendorNetPayout = $order->amount - $commissionAmount;

            $vendorWallet = $this->getWallet($vendor);
            if ($vendorWallet->pending_balance >= $vendorNetPayout) {
                $vendorWallet->pending_balance -= $vendorNetPayout;
                $vendorWallet->save();
            }

            // 3. Reclaim funds from Platform Account (Buyer Protection + Commission + Shipping if held)
            $platformRevenue = ($order->total_amount - $order->amount) + ($order->platform_commission ?? 0);
            if ($platformRevenue > 0) {
                $platformWallet = $this->getPlatformWallet();
                
                // We debit the platform the amount they collected
                if ($platformWallet->balance >= $platformRevenue) {
                     $this->debitWallet($platformWallet, $platformRevenue, 'refund_debit', "Refund reversal for Order #{$order->id}", "order_refund_rev_{$order->id}");
                }
            }

            // 4. Credit Buyer
            $this->credit($buyer, $refundAmount, 'refund', "Order #{$order->id} cancelled - Refund", "order_refund_{$order->id}");
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

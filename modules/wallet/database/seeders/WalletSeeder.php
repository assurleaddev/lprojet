<?php

namespace Modules\Wallet\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\Transaction;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $user = User::find(1);

        if ($user) {
            // Ensure wallet exists
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            // Clear existing transactions for idempotency (optional, but good for testing)
            Transaction::where('wallet_id', $wallet->id)->delete();

            // 1. Initial Deposit (+1000)
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => 1000,
                'description' => 'Initial Deposit',
                'reference_id' => 'DEP-' . uniqid(),
            ]);

            // 2. Purchase (-200)
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => 200,
                'description' => 'Purchase of Item #123',
                'reference_id' => 'PUR-' . uniqid(),
            ]);

            // 3. Sale (+500)
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => 500,
                'description' => 'Sale of Item #456',
                'reference_id' => 'SAL-' . uniqid(),
            ]);

            // Update Balance to 1300 (1000 - 200 + 500)
            $wallet->update(['balance' => 1300]);

            $this->command->info('Wallet seeded for User ID 1 with balance 1300.');
        } else {
            $this->command->warn('User ID 1 not found. Wallet seeding skipped.');
        }
    }
}

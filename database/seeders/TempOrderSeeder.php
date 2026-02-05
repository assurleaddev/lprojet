<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

class TempOrderSeeder extends Seeder
{
    public function run()
    {
        $user1 = User::find(1);
        $otherUser = User::where('id', '!=', 1)->first();

        if (!$user1) {
            $this->command->error("User 1 not found.");
            return;
        }
        if (!$otherUser) {
            $this->command->error("No other user found to be the counterpart.");
            return;
        }

        // 1. Purchase: User 1 buys a product from someone else
        $productToBuy = Product::where('vendor_id', '!=', 1)->first();
        if ($productToBuy) {
            Order::create([
                'user_id' => $user1->id,
                'vendor_id' => $productToBuy->vendor_id,
                'product_id' => $productToBuy->id,
                'amount' => $productToBuy->price ?? 10.00,
                'status' => 'pending'
            ]);
            $this->command->info("Purchase created: User 1 bought '{$productToBuy->name}'");
        } else {
            $this->command->warn("No product found for User 1 to buy (needs a product from another vendor).");
        }

        // 2. Sale: Someone buys User 1's product
        $productToSell = Product::where('vendor_id', 1)->first();
        if ($productToSell) {
            Order::create([
                'user_id' => $otherUser->id,
                'vendor_id' => $user1->id,
                'product_id' => $productToSell->id,
                'amount' => $productToSell->price ?? 20.00,
                'status' => 'delivered'
            ]);
            $this->command->info("Sale created: User 1 sold '{$productToSell->name}'");
        } else {
            $this->command->warn("User 1 has no products to sell. Please list an item for User 1 first.");
        }
    }
}

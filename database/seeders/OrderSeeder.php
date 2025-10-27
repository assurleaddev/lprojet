<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class OrderSeeder extends Seeder
{
     /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products that have a vendor
        $products = Product::whereNotNull('vendor_id')->get();

        // Get all users who are not vendors to act as customers
        $customers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Editor');
        })->get();

        if ($products->isEmpty() || $customers->isEmpty()) {
            $this->command->info('Could not create orders. Please make sure you have products and non-vendor users in your database.');
            return;
        }

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        $this->command->info('Creating sample orders...');

        for ($i = 0; $i < 50; $i++) {
            // Pick a random product
            $product = $products->random();
            // Pick a random customer
            $customer = $customers->random();

            // Ensure the vendor is not buying their own product
            if ($customer->id === $product->vendor_id) {
                continue;
            }

            Order::create([
                'user_id'    => $customer->id,
                'product_id' => $product->id,
                'vendor_id'  => $product->vendor_id,
                'amount'     => $product->price,
                'status'     => $statuses[array_rand($statuses)],
                'created_at' => now()->subDays(rand(0, 30)), // Create orders over the last month
            ]);
        }

        $this->command->info('50 sample orders have been created.');
    }
}

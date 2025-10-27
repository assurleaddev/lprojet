<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Get all categories to associate with products
        $categories = Category::all();

        // Create products
        Product::factory()
            ->count(50) // Adjust the number of products as needed
            ->create([
                'category_id' => $categories->random()->id, // Randomly assign a category
                'vendor_id' => 1, // Assuming a vendor with ID 1 exists
                'status' => 'approved', // Set default status
            ])->each(function ($product) {
                // Attach options if needed
                // $product->options()->attach([...]);

                // Add images and a featured image
                $product->addMediaFromUrl('https://via.placeholder.com/300')->toMediaCollection('featured');
                for ($i = 0; $i < 3; $i++) {
                    $product->addMediaFromUrl('https://via.placeholder.com/300')->toMediaCollection('products');
                }
            });
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Assuming you have some categories already in the database
        $categories = Category::all();

        foreach ($categories as $category) {
            // Create a product for each category
            $product = Product::create([
                'name' => $faker->word . ' ' . $faker->word,
                'description' => $faker->paragraph,
                'price' => $faker->randomFloat(2, 10, 1000),
                'vendor_id' => 1, // Assuming vendor_id 1 exists
                'category_id' => $category->id,
                'status' => 'approved',
            ]);

            // Attach options if needed
            // $product->options()->attach([...]);

            // Add a featured image
            $product->addMediaFromUrl($faker->imageUrl(640, 480, 'business', true, 'Faker'))
                ->toMediaCollection('featured');

            // Add multiple images
            for ($i = 0; $i < 3; $i++) {
                $product->addMediaFromUrl($faker->imageUrl(640, 480, 'business', true, 'Faker'))
                    ->toMediaCollection('products');
            }
        }
    }
}
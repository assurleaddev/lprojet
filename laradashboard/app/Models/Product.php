<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Option;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Assuming you have categories and options already created
        $categories = Category::all();
        $options = Option::all();

        foreach (range(1, 50) as $index) {
            $product = Product::create([
                'name' => $faker->word . ' ' . $faker->word,
                'description' => $faker->paragraph,
                'price' => $faker->randomFloat(2, 1, 1000),
                'vendor_id' => 1, // Assuming a vendor with ID 1 exists
                'category_id' => $categories->random()->id,
                'status' => $faker->randomElement(['approved', 'pending']),
            ]);

            // Attach random options to the product
            if ($options->count() > 0) {
                $product->options()->attach($options->random(rand(1, 3))->pluck('id')->toArray());
            }

            // Add images and a featured image
            $product->addMediaFromUrl($faker->imageUrl())->toMediaCollection('products');
            $product->addMediaFromUrl($faker->imageUrl())->toMediaCollection('featured');
        }
    }
}
<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'vendor_id' => 1, // Assuming a default vendor ID for seeding
            'category_id' => Category::factory(), // Create a category for the product
            'status' => $this->faker->randomElement(['approved', 'pending']),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            // Attach options, images, and a featured image
            $product->options()->attach([1, 2]); // Assuming option IDs 1 and 2 exist

            // Add images
            $product->addMedia($this->faker->imageUrl())->toMediaCollection('products');
            $product->addMedia($this->faker->imageUrl())->toMediaCollection('products');
            $product->addMedia($this->faker->imageUrl())->toMediaCollection('products');

            // Add a featured image
            $product->addMedia($this->faker->imageUrl())->toMediaCollection('featured');
        });
    }
}
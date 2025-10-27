<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Media; // Your custom Media model
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage; // Make sure to import Storage

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $categories = Category::with('attributes.options')->get();
        $users = User::role('vendor')->get();
        $mediaItems = Media::all(); // Get all media items once to be more efficient

        if ($mediaItems->count() < 7) {
            $this->command->error('Not enough media items in the database to run the seeder. Please upload at least 7 images.');
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            $category = $categories->random();
            $vendor = $users->random();

            // --- Product Creation (No changes here) ---
            $optionIds = [];
            foreach ($category->attributes as $attribute) {
                if ($attribute->options->isNotEmpty()) {
                    $option = $attribute->options->random();
                    $optionIds[] = $option->id;
                }
            }

            $product = Product::create([
                'name' => $faker->words(3, true),
                'description' => $faker->paragraph,
                'price' => $faker->randomFloat(2, 10, 1000),
                'category_id' => $category->id,
                'vendor_id' => $vendor->id,
                'status' => 'approved',
            ]);

            $product->options()->sync($optionIds);

            // --- Correct and More Robust Media Association Logic ---

            // 1. Select a random set of media items for this product
            $selectedMedia = $mediaItems->random(rand(3, 7));
            
            // 2. Pick one to be the featured image
            $featuredMediaItem = $selectedMedia->first();

            // 3. Loop through the selected media and add them from their path
            foreach ($selectedMedia as $mediaItem) {
                // This check is important. It ensures the file actually exists before we try to add it.
                if (!file_exists($mediaItem->getPath())) {
                    continue;
                }
                
                $collectionName = ($mediaItem->id === $featuredMediaItem->id) ? 'featured' : 'products';

                // **THE FIX IS HERE**
                // We use addMedia() with the path from the existing media item.
                // ->preservingOriginal() is vital to prevent the source file from being deleted.
                $product->addMedia($mediaItem->getPath())
                        ->preservingOriginal()
                        ->toMediaCollection($collectionName);
            }
        }
    }
}
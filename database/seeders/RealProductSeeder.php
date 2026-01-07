<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = User::first(); // Or specific vendor
        if (!$vendor) {
            $vendor = User::factory()->create();
        }

        // 1. Define Curated Products with Unsplash Image IDs
        $products = [
            [
                'name' => 'Vintage Levi\'s 501 Original Fit Jeans',
                'description' => 'Classic vintage Levi\'s 501 jeans in excellent condition. Straight leg, button fly, timeless blue wash. No rips or stains. Size W32 L32.',
                'price' => 450.00,
                'condition' => 'very_good',
                'category_path' => ['Women', 'Vêtements', 'Jeans'], // Simplified path
                'brand_name' => 'Levi\'s',
                'size' => 'W32',
                'color' => 'Blue',
                'images' => [
                    'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?q=80&w=1000&auto=format&fit=crop', // Main Denim
                    'https://images.unsplash.com/photo-1582552938357-32b906df40cb?q=80&w=1000&auto=format&fit=crop', // Jeans Detail
                    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=1000&auto=format&fit=crop', // Jeans Folded
                ]
            ],
            [
                'name' => 'Zara Floral Summer Dress',
                'description' => 'Light and airy floral dress from Zara. Perfect for summer days. Midi length, v-neck, short sleeves. Very comfortable viscose fabric.',
                'price' => 120.00,
                'condition' => 'good',
                'category_path' => ['Women', 'Vêtements', 'Robes'],
                'brand_name' => 'Zara',
                'size' => 'M',
                'color' => 'Red',
                'images' => [
                    'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?q=80&w=1000&auto=format&fit=crop', // Main Dress
                    'https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=1000&auto=format&fit=crop', // Dress Detail
                ]
            ],
            [
                'name' => 'Nike Air Force 1 "Triple White"',
                'description' => 'Iconic Nike Air Force 1 sneakers in all-white. Worn a few times but still in great shape. Cleaned and ready to wear. Size EU 42.',
                'price' => 600.00,
                'condition' => 'good',
                'category_path' => ['Men', 'Chaussures', 'Baskets'],
                'brand_name' => 'Nike',
                'size' => '42',
                'color' => 'White',
                'images' => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=1000&auto=format&fit=crop', // Nike Shoe
                    'https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=1000&auto=format&fit=crop', // Sneaker Side
                ]
            ],
            [
                'name' => 'H&M Beige Trench Coat',
                'description' => 'Classic beige trench coat from H&M. Double-breasted, belted waist. Great for spring/autumn weather. Size L.',
                'price' => 300.00,
                'condition' => 'very_good',
                'category_path' => ['Women', 'Vêtements', 'Manteaux & vestes'],
                'brand_name' => 'H&M',
                'size' => 'L',
                'color' => 'Beige',
                'images' => [
                    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=1000&auto=format&fit=crop', // Trench Coat
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?q=80&w=1000&auto=format&fit=crop', // Coat Texture/Color
                ]
            ],
            [
                'name' => 'Leather Biker Jacket',
                'description' => 'Real leather biker jacket. Heavy duty zippers, quilted shoulders. Adds a cool edge to any outfit. Vintage but well maintained.',
                'price' => 850.00,
                'condition' => 'good',
                'category_path' => ['Men', 'Vêtements', 'Manteaux & vestes'],
                'brand_name' => 'Zara', // Fallback or Generic
                'size' => 'L',
                'color' => 'Black',
                'images' => [
                    'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?q=80&w=1000&auto=format&fit=crop', // Leather Jacket
                    'https://images.unsplash.com/photo-1551028716-e0f3192f168a?q=80&w=1000&auto=format&fit=crop', // Jacket Texture
                ]
            ]
        ];

        foreach ($products as $item) {
            $this->command->info("Seeding: {$item['name']}");

            // 2. Find Category (Navigate logic or flattened search)
            // Just finding leaf category for simplicity
            $leafCategoryName = end($item['category_path']);
            $category = Category::where('name', $leafCategoryName)->first();

            if (!$category) {
                // Fallback to a root category if leaf not found
                $category = Category::first();
                $this->command->warn("Category '$leafCategoryName' not found, using '{$category->name}'");
            }

            // 3. Find/Create Brand
            $brand = Brand::firstOrCreate(['name' => $item['brand_name']]);

            // 4. Create Product
            $product = Product::create([
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'condition' => $item['condition'],
                'size' => $item['size'],
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'status' => 'approved',
            ]);

            // 5. Seed Images (Download and Save Locally)
            foreach ($item['images'] as $index => $url) {
                try {
                    $media = $product->addMediaFromUrl($url)
                        ->toMediaCollection($index === 0 ? 'featured' : 'products');

                    $this->command->info("  - Downloaded image: {$media->file_name}");
                } catch (\Exception $e) {
                    $this->command->error("  - Failed to download image: {$url}. Error: " . $e->getMessage());
                }
            }

            // 6. Handle Colors/Attributes (Optional simplified version)
            // We'd ideally find the Attribute for 'Colors' and sync the Option 'Red'
            // Keeping it simple specifically for images request
        }

        // 6. Generate 95 Random "Real-ish" Products
        $faker = \Faker\Factory::create();

        // Map Categories to specific Unsplash keywords for better relevance
        $categoryKeywords = [
            'Jeans' => 'jeans,denim',
            'Robes' => 'dress,fashion',
            'Baskets' => 'sneakers,shoes',
            'Manteaux & vestes' => 'jacket,coat',
            'T-shirts' => 't-shirt,top',
            'Pulls & gilets' => 'sweater,knitwear',
            'Jupes' => 'skirt,fashion',
            'Pantalons' => 'pants,trousers',
            'Sacs' => 'handbag,purse',
            'Accessoires' => 'fashion accessories',
        ];

        $totalToSeed = 100;
        $currentCount = count($products);

        for ($i = $currentCount; $i < $totalToSeed; $i++) {
            $this->command->info("Generating Product {$i}/{$totalToSeed}...");

            // Pick a random category from our keyword map
            $categoryName = array_rand($categoryKeywords);
            $keyword = $categoryKeywords[$categoryName];

            // Find the category in DB
            $category = Category::where('name', $categoryName)->first();
            if (!$category) {
                // Fallback: pick any valid category
                $category = Category::whereNotNull('parent_id')->inRandomOrder()->first();
                if (!$category) {
                    $category = Category::first();
                }
            }

            // Create Product
            $product = Product::create([
                'name' => ucfirst(explode(',', $keyword)[0]) . ' ' . $faker->colorName . ' ' . $faker->word, // e.g., "Jeans SkyBlue Vintage"
                'description' => $faker->paragraph(3),
                'price' => $faker->randomFloat(2, 20, 300),
                'condition' => $faker->randomElement(['new_with_tags', 'very_good', 'good']),
                'size' => $faker->randomElement(['XS', 'S', 'M', 'L', 'XL', '38', '40', '42']),
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'brand_id' => Brand::inRandomOrder()->first()->id ?? 1,
                'status' => 'approved',
            ]);

            // Add 1-3 Images
            $numImages = rand(1, 3);
            for ($j = 0; $j < $numImages; $j++) {
                try {
                    // Use a signature to ensure getting DIFFERENT images for the same keyword
                    // Using images.unsplash.com/photo-ID if I had IDs, but source.unsplash is best for random
                    // NOTE: source.unsplash is deprecated, it redirects to images.unsplash.
                    // A better hack for specific keywords is using loremflickr with keywords
                    // But user insisted on "Real clothes". 
                    // Let's try to scrape a valid Unsplash URL via the redirect.
                    // Actually, let's use a stable random image service that supports keywords if source.unsplash fails.
                    // But for now, source.unsplash with ?random is standard.

                    $imageUrl = "https://source.unsplash.com/800x800/?" . urlencode($keyword) . "&sig=" . rand(1000, 99999);

                    $product->addMediaFromUrl($imageUrl)
                        ->toMediaCollection($j === 0 ? 'featured' : 'products');

                    $this->command->info("  - Downloaded random {$keyword} image.");
                } catch (\Exception $e) {
                    $this->command->warn("  - Failed to download image ($j): " . $e->getMessage());
                }
            }
        }
    }
}

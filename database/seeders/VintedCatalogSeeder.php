<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Option;
use App\Models\Brand; // Ensure this model exists

class VintedCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // 1. Load the JSON data
            // For simplicity, I'm embedding the data structure logic here, based on what we planned.
            // In a real app, you might read the .json file directly.

            // We can create Brands if needed by the schema, although Brands are global.
            // Let's seed a few example Brands for the search.
            $this->seedBrands();

            // 2. Define Attributes (Global & Specific)
            $attributeMap = $this->seedAttributes();

            // 3. Define the Category Tree
            $categories = $this->getCategoryTree();

            // 4. Insert Categories recursively and attach attributes
            foreach ($categories as $catData) {
                $this->createCategory($catData, null, $attributeMap);
            }

            $this->command->info('Vinted Catalog Seeded Successfully!');
        } catch (\Exception $e) {
            $this->command->error("Seeder Failed: " . $e->getMessage());
            file_put_contents('seeder_error.log', $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    private function seedBrands()
    {
        $brands = ['Zara', 'H&M', 'Nike', 'Adidas', 'Gucci', 'Vintage', 'Uniqlo', 'Levi\'s'];

        if (class_exists('App\Models\Brand')) {
            foreach ($brands as $name) {
                // Use fully qualified name to be safe
                \App\Models\Brand::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
            }
        } else {
            foreach ($brands as $name) {
                $exists = DB::table('brands')->where('name', $name)->exists();
                if (!$exists) {
                    DB::table('brands')->insert([
                        'name' => $name,
                        'slug' => Str::slug($name),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    private function seedAttributes()
    {
        $map = [];

        // --- GLOBAL COLORS ---
        $colors = ["Black", "White", "Gray", "Cream", "Beige", "Red", "Blue", "Green", "Yellow", "Multicolor"];
        $colorAttr = Attribute::where('name', 'Colors')->first();
        if (!$colorAttr) {
            $colorAttr = Attribute::create(['name' => 'Colors', 'type' => 'color', 'code' => 'colors']);
        }
        $this->syncOptions($colorAttr, $colors);
        $map['Colors'] = $colorAttr;


        // --- SIZES ---
        // Women's Sizes
        $womenSizes = ["XXS / 32", "XS / 34", "S / 36", "M / 38", "L / 40", "XL / 42", "XXL / 44"];
        $wsAttr = Attribute::where('name', "Women's Clothes Size")->first();
        if (!$wsAttr) {
            $wsAttr = Attribute::create(['name' => "Women's Clothes Size", 'type' => 'select', 'code' => 'size_women_clothes']);
        }
        $this->syncOptions($wsAttr, $womenSizes);
        $map["Women's Clothes Size"] = $wsAttr;

        // Women's Shoe Sizes
        $womenShoeSizes = ["36", "37", "38", "39", "40", "41"];
        $wssAttr = Attribute::where('name', "Women's Shoe Size")->first();
        if (!$wssAttr) {
            $wssAttr = Attribute::create(['name' => "Women's Shoe Size", 'type' => 'select', 'code' => 'size_women_shoes']);
        }
        $this->syncOptions($wssAttr, $womenShoeSizes);
        $map["Women's Shoe Size"] = $wssAttr;

        // Men's Sizes
        $menSizes = ["XS", "S", "M", "L", "XL", "XXL", "3XL"];
        $msAttr = Attribute::where('name', "Men's Clothes Size")->first();
        if (!$msAttr) {
            $msAttr = Attribute::create(['name' => "Men's Clothes Size", 'type' => 'select', 'code' => 'size_men_clothes']);
        }
        $this->syncOptions($msAttr, $menSizes);
        $map["Men's Clothes Size"] = $msAttr;

        // Men's Shoe Sizes
        $menShoeSizes = ["39", "40", "41", "42", "43", "44", "45", "46"];
        $mssAttr = Attribute::where('name', "Men's Shoe Size")->first();
        if (!$mssAttr) {
            $mssAttr = Attribute::create(['name' => "Men's Shoe Size", 'type' => 'select', 'code' => 'size_men_shoes']);
        }
        $this->syncOptions($mssAttr, $menShoeSizes);
        $map["Men's Shoe Size"] = $mssAttr;

        // Kids Sizes
        $kidsSizes = ["Newborn", "0-3 m", "3-6 m", "6-9 m", "9-12 m", "12-18 m", "18-24 m", "2-3 y", "3-4 y"];
        $ksAttr = Attribute::where('name', "Kids' Clothing Size")->first();
        if (!$ksAttr) {
            $ksAttr = Attribute::create(['name' => "Kids' Clothing Size", 'type' => 'select', 'code' => 'size_kids_clothes']);
        }
        $this->syncOptions($ksAttr, $kidsSizes);
        $map["Kids' Clothing Size"] = $ksAttr;

        // Materials
        $materials = ["Cotton", "Denim", "Leather", "Wool", "Silk", "Polyester", "Linen"];
        $matAttr = Attribute::where('name', "Material")->first();
        if (!$matAttr) {
            $matAttr = Attribute::create(['name' => "Material", 'type' => 'select', 'code' => 'material']);
        }
        $this->syncOptions($matAttr, $materials);
        $map["Material"] = $matAttr;

        return $map;
    }

    private function syncOptions($attribute, $values)
    {
        foreach ($values as $val) {
            $exists = Option::where('attribute_id', $attribute->id)->where('value', $val)->exists();
            if (!$exists) {
                Option::create([
                    'attribute_id' => $attribute->id,
                    'value' => $val
                ]);
            }
        }
    }

    private function createCategory($data, $parent = null, $attributeMap)
    {
        // Try to handle duplicate slugs: e.g. Shoes (Women) vs Shoes (Men)
        $baseSlug = Str::slug($data['name']);
        if ($parent) {
            // To ensure uniqueness, we can prefix parent slug if collision happens, 
            // but Vinted URLs are often like /women/shoes/sneakers.
            // For now, let's keep it simple: slug = base name. If collision, Laravel might error unless we handle unique.
            // Let's create a composite slug if needed: parent-child.
            // But category structure requires unique slugs per table usually.

            // Check if slug exists
            $slug = $baseSlug;
            if (Category::where('slug', $slug)->exists()) {
                $slug = $parent->slug . '-' . $baseSlug;
            }
        } else {
            $slug = $baseSlug;
        }

        // Manual check for Category to avoid ambiguous column error in updateOrCreate
        $query = Category::where('name', $data['name']);
        if ($parent) {
            $query->where('parent_id', $parent->id);
        } else {
            $query->whereNull('parent_id');
        }

        $category = $query->first();

        if (!$category) {
            $category = Category::create([
                'name' => $data['name'],
                'parent_id' => $parent ? $parent->id : null,
                'slug' => $slug
            ]);
        } else {
            // Update slug if needed, or just touch
            if ($category->slug != $slug) {
                $category->update(['slug' => $slug]);
            }
        }

        // Attach Attributes
        if (isset($data['attributes'])) {
            $attrIds = [];
            foreach ($data['attributes'] as $attrName) {
                if (isset($attributeMap[$attrName])) {
                    $attrIds[] = $attributeMap[$attrName]->id;
                }
            }
            if (!empty($attrIds)) {
                $category->assignedAttributes()->syncWithoutDetaching($attrIds);
            }
        }

        // Always attach Colors to top-level or second-level categories?
        // Let's attach 'Colors' to the 'Clothes' and 'Shoes' sub-roots if we want them inherited.
        // For simplicity, let's attach to all Leaf nodes or specifically where logical.
        // Or simpler: Attach 'Colors' to 'Women', 'Men', 'Kids' root.
        if ($parent === null) {
            if (isset($attributeMap['Colors'])) {
                $category->assignedAttributes()->syncWithoutDetaching([$attributeMap['Colors']->id]);
            }
        }


        // Recurse children
        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                // Determine structure: child might be string or array
                if (is_string($child)) {
                    $childData = ['name' => $child];
                } else {
                    $childData = $child;
                }
                $this->createCategory($childData, $category, $attributeMap);
            }
        }
    }

    private function getCategoryTree()
    {
        // Paste the abbreviated JSON logic here or full logic
        // Using the logic derived from step 67
        return [
            [
                "name" => "Women",
                "children" => [
                    [
                        "name" => "Clothes",
                        "attributes" => ["Women's Clothes Size", "Material"],
                        "children" => ["Coats & Jackets", "Dresses", "Tops & T-shirts", "Jeans", "Skirts"]
                    ],
                    [
                        "name" => "Shoes",
                        "attributes" => ["Women's Shoe Size"],
                        "children" => ["Boots", "Sneakers", "Heels", "Sandals"]
                    ],
                    [
                        "name" => "Bags",
                        "children" => ["Handbags", "Backpacks", "Tote bags"]
                    ]
                ]
            ],
            [
                "name" => "Men",
                "children" => [
                    [
                        "name" => "Clothes",
                        "attributes" => ["Men's Clothes Size", "Material"],
                        "children" => ["Coats & Jackets", "Tops & T-shirts", "Jeans", "Suits & Blazers"]
                    ],
                    [
                        "name" => "Shoes",
                        "attributes" => ["Men's Shoe Size"],
                        "children" => ["Sneakers", "Boots", "Formal shoes"]
                    ]
                ]
            ],
            [
                "name" => "Kids",
                "children" => [
                    [
                        "name" => "Girls' Clothing",
                        "attributes" => ["Kids' Clothing Size"],
                        "children" => ["Dresses", "Skirts", "Tops"]
                    ],
                    [
                        "name" => "Boys' Clothing",
                        "attributes" => ["Kids' Clothing Size"],
                        "children" => ["Tops", "Jumpers", "Trousers"]
                    ]
                ]
            ]
        ];
    }
}

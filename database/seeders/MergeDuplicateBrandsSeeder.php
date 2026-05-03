<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MergeDuplicateBrandsSeeder extends Seeder
{
    public function run(): void
    {
        // Sort brands shortest-name-first so parent brands are always processed before sub-brands.
        // A sub-brand is any brand whose name starts with another brand's name followed by a space.
        // e.g. "Nike Air" and "Nike Air Max" are sub-brands of "Nike".
        $brands = Brand::orderByRaw('LENGTH(name) ASC')->get();

        $merged = 0;
        $reassigned = 0;

        foreach ($brands as $parent) {
            $parentName = trim($parent->name);

            // The trailing space in the prefix prevents "Nikeland" from matching "Nike".
            $subBrands = $brands->filter(
                fn (Brand $b) => $b->id !== $parent->id
                && stripos(trim($b->name), $parentName.' ') === 0
            );

            foreach ($subBrands as $sub) {
                $productCount = Product::where('brand_id', $sub->id)->count();

                if ($productCount > 0) {
                    Product::where('brand_id', $sub->id)->update(['brand_id' => $parent->id]);
                    $reassigned += $productCount;
                    $this->command->line("  Reassigned {$productCount} product(s): \"{$sub->name}\" → \"{$parent->name}\"");
                }

                $sub->delete();
                $merged++;
                $this->command->line("  Deleted sub-brand \"{$sub->name}\"");
            }
        }

        $this->command->info("Done. Merged {$merged} sub-brand(s), reassigned {$reassigned} product(s).");
    }
}

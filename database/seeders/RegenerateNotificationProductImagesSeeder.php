<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegenerateNotificationProductImagesSeeder extends Seeder
{
    private array $productCache = [];

    public function run(): void
    {
        $typesWithProduct = ['product_liked', 'price_change', 'new_product'];

        DB::table('notifications')
            ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type'))"), $typesWithProduct)
            ->whereNull(DB::raw("JSON_EXTRACT(data, '$.product_image')"))
            ->orderBy('created_at', 'desc')
            ->chunk(100, function ($notifications) {
                foreach ($notifications as $notification) {
                    $data = json_decode($notification->data, true);
                    $productId = $data['product_id'] ?? null;

                    if (! $productId) {
                        continue;
                    }

                    $product = $this->getProduct($productId);

                    if (! $product) {
                        continue;
                    }

                    $data['product_image'] = $product->getFeaturedImageUrl('thumb');

                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update(['data' => json_encode($data)]);
                }
            });

        $this->command->info('Notification product images regenerated successfully.');
    }

    private function getProduct(int $id): ?Product
    {
        if (! array_key_exists($id, $this->productCache)) {
            $this->productCache[$id] = Product::find($id);
        }

        return $this->productCache[$id];
    }
}

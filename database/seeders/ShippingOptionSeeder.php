<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingOption;

class ShippingOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $options = [
            [
                'type' => 'home_pickup',
                'label' => 'Royal Mail',
                'description' => 'Home collection. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-red-600',
                'key' => 'shipping_royal_mail',
                'is_active' => true,
            ],
            [
                'type' => 'home_pickup',
                'label' => 'Evri',
                'description' => 'Home collection. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-blue-600',
                'key' => 'shipping_evri_home',
                'is_active' => true,
            ],
            [
                'type' => 'home_pickup',
                'label' => 'Yodel Direct',
                'description' => 'Home collection. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-green-600',
                'key' => 'shipping_yodel_home',
                'is_active' => true,
            ],
            [
                'type' => 'home_pickup',
                'label' => 'DPD',
                'description' => 'Home collection. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-red-500',
                'key' => 'shipping_dpd',
                'is_active' => true,
            ],
            // Drop off
            [
                'type' => 'drop_off',
                'label' => 'Evri',
                'description' => 'ParcelShop drop-off. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-blue-600',
                'key' => 'shipping_evri_shop',
                'is_active' => true,
            ],
            [
                'type' => 'drop_off',
                'label' => 'Yodel Direct',
                'description' => 'Store drop-off. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-green-600',
                'key' => 'shipping_yodel_shop',
                'is_active' => true,
            ],
            [
                'type' => 'drop_off',
                'label' => 'InPost',
                'description' => 'Locker drop-off. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-yellow-400',
                'key' => 'shipping_inpost',
                'is_active' => true,
            ],
            [
                'type' => 'drop_off',
                'label' => 'UPS',
                'description' => 'Access Point™ drop-off. <a href="#" class="underline">Learn more</a>',
                'icon_class' => 'bg-amber-700',
                'key' => 'shipping_ups',
                'is_active' => true,
            ],
        ];

        foreach ($options as $option) {
            ShippingOption::updateOrCreate(
                ['key' => $option['key']],
                $option
            );
        }
    }
}

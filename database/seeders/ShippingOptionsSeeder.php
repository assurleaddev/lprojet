<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingOption;

class ShippingOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $options = [
            // Drop-off points
            [
                'key' => 'shipping_inpost_locker',
                'label' => '24/7 InPost Locker | Shop Pick-up',
                'description' => 'Prepaid label. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-yellow-400',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_dpd_pickup',
                'label' => 'DPD Pickup',
                'description' => 'Prepaid drop-off QR code. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-red-600',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_evri_home',
                'label' => 'Evri Home Delivery',
                'description' => 'Prepaid label. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-blue-400',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_evri_parcelshop',
                'label' => 'Evri ParcelShop',
                'description' => 'Prepaid label. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-blue-400',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_inpost_home',
                'label' => 'InPost Home Delivery',
                'description' => 'Prepaid label (print-at-home only). Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-yellow-400',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_relay_home',
                'label' => 'Relay Home Delivery',
                'description' => 'Prepaid drop-off QR code. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-black',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_relay_pickup',
                'label' => 'Relay Shop Pick-up',
                'description' => 'Prepaid drop-off QR code. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-black',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_royal_mail',
                'label' => 'Royal Mail',
                'description' => 'Prepaid drop-off QR code. Find your nearest drop-off point <a href="#" class="underline">here</a> (excluding Postboxes).',
                'icon_class' => 'bg-red-500',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_yodel_door',
                'label' => 'Yodel Store to Door',
                'description' => 'Prepaid label. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-green-500',
                'type' => 'drop_off',
            ],
            [
                'key' => 'shipping_yodel_store',
                'label' => 'Yodel Store to Store',
                'description' => 'Prepaid label. Find your nearest drop-off point <a href="#" class="underline">here</a>.',
                'icon_class' => 'bg-green-500',
                'type' => 'drop_off',
            ],
            // Home pickup
            [
                'key' => 'shipping_anyvan',
                'label' => 'AnyVan',
                'description' => 'Home to home delivery. Includes tracking.',
                'icon_class' => 'bg-blue-500',
                'type' => 'home_pickup',
            ],
            [
                'key' => 'shipping_yodel_door_to_door',
                'label' => 'Yodel Door to Door',
                'description' => 'Prepaid label (print-at-home only). Parcel collected from your address.',
                'icon_class' => 'bg-green-500',
                'type' => 'home_pickup',
            ],
        ];

        foreach ($options as $option) {
            ShippingOption::updateOrCreate(['key' => $option['key']], $option);
        }
    }
}

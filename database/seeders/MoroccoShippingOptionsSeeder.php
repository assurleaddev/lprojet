<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingOption;

class MoroccoShippingOptionsSeeder extends Seeder
{
    public function run(): void
    {
        // Remove UK carriers that are irrelevant for Morocco
        $ukKeys = [
            'shipping_inpost_locker', 'shipping_dpd_pickup', 'shipping_evri_home',
            'shipping_evri_parcelshop', 'shipping_inpost_home', 'shipping_relay_home',
            'shipping_relay_pickup', 'shipping_royal_mail', 'shipping_yodel_door',
            'shipping_yodel_store', 'shipping_anyvan', 'shipping_yodel_door_to_door',
            'shipping_evri_shop', 'shipping_yodel_shop', 'shipping_inpost',
            'shipping_ups', 'shipping_yodel_home', 'shipping_dpd', 'shipping_evri',
        ];
        ShippingOption::whereIn('key', $ukKeys)->delete();

        $options = [
            // Drop-off / agency delivery
            [
                'key' => 'shipping_amana',
                'label' => 'Amana',
                'description' => 'Livraison à domicile ou en agence. <a href="#" class="underline">En savoir plus</a>',
                'icon_class' => 'bg-orange-500',
                'type' => 'drop_off',
                'price' => 30.00,
                'is_active' => true,
            ],
            [
                'key' => 'shipping_laposte_maroc',
                'label' => 'Al Barid Bank / Poste Maroc',
                'description' => 'Envoi postal avec suivi. <a href="#" class="underline">En savoir plus</a>',
                'icon_class' => 'bg-yellow-500',
                'type' => 'drop_off',
                'price' => 25.00,
                'is_active' => true,
            ],
            [
                'key' => 'shipping_colis_prive',
                'label' => 'Colis Privé',
                'description' => 'Livraison rapide en 24-48h. <a href="#" class="underline">En savoir plus</a>',
                'icon_class' => 'bg-blue-500',
                'type' => 'drop_off',
                'price' => 35.00,
                'is_active' => true,
            ],
            // Home pickup
            [
                'key' => 'shipping_amana_home',
                'label' => 'Amana – Collecte à domicile',
                'description' => 'Un livreur Amana collecte le colis chez vous. <a href="#" class="underline">En savoir plus</a>',
                'icon_class' => 'bg-orange-500',
                'type' => 'home_pickup',
                'price' => 40.00,
                'is_active' => true,
            ],
            [
                'key' => 'shipping_chronopost_maroc',
                'label' => 'Chronopost Maroc',
                'description' => 'Collecte et livraison express. <a href="#" class="underline">En savoir plus</a>',
                'icon_class' => 'bg-red-600',
                'type' => 'home_pickup',
                'price' => 50.00,
                'is_active' => true,
            ],
        ];

        foreach ($options as $option) {
            ShippingOption::updateOrCreate(['key' => $option['key']], $option);
        }
    }
}

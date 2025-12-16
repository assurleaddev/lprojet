<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1;
        $now = Carbon::now();

        $notifications = [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\NewProductNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'message' => 'New product from Nike: Air Jordan 1',
                    'url' => '/products/1',
                ]),
                'read_at' => null,
                'created_at' => $now->copy()->subMinutes(5),
                'updated_at' => $now->copy()->subMinutes(5),
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\PriceChangeNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'message' => 'Price drop! iPhone 13 is now $599',
                    'url' => '/products/2',
                ]),
                'read_at' => null,
                'created_at' => $now->copy()->subMinutes(30),
                'updated_at' => $now->copy()->subMinutes(30),
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\NewFollowerNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'message' => 'John Doe started following you.',
                    'url' => '/profile/2',
                ]),
                'read_at' => null,
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now->copy()->subHours(2),
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\ProductLikedNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'message' => 'Jane Smith liked your product Vintage Jacket.',
                    'url' => '/products/3',
                ]),
                'read_at' => $now->copy()->subHours(5), // Read
                'created_at' => $now->copy()->subHours(5),
                'updated_at' => $now->copy()->subHours(5),
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\OrderUpdateNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'message' => 'Your order #12345 has been shipped.',
                    'url' => '/orders/12345',
                ]),
                'read_at' => null,
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\OfferNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'message' => 'New offer on Sony Headphones: $150',
                    'url' => '/chat?id=1',
                ]),
                'read_at' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],
        ];

        DB::table('notifications')->insert($notifications);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('is_system', true)->exists()) {
            $this->command->info('Platform system user already exists — skipping.');
            return;
        }

        User::create([
            'first_name' => 'Platform',
            'last_name' => '',
            'email' => 'platform@system.local',
            'username' => 'platform',
            'password' => Hash::make(Str::random(32)),
            'is_system' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Platform system user created.');
    }
}

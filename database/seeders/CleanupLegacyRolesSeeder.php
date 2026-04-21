<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CleanupLegacyRolesSeeder extends Seeder
{
    public function run(): void
    {
        $legacy = ['Admin', 'Editor', 'Subscriber', 'Contact', 'vendor'];

        foreach ($legacy as $name) {
            $role = Role::findByName($name, 'web');

            if ($role) {
                // Detach from all users before deleting
                $role->users()->detach();
                $role->delete();
                $this->command->info("Deleted role: {$name}");
            }
        }

        $this->command->info('Legacy roles removed.');
    }
}

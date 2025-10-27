<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MarketplacePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Use firstOrCreate to be safe, but with correct capitalization
        $superAdmin = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);

        // Create marketplace permissions
        Permission::firstOrCreate(['name' => 'products.manage', 'guard_name' => 'web','marketplace']);
        Permission::firstOrCreate(['name' => 'products.approve', 'guard_name' => 'web','marketplace']);
        Permission::firstOrCreate(['name' => 'attributes.manage', 'guard_name' => 'web','marketplace']);
        Permission::firstOrCreate(['name' => 'categories.manage', 'guard_name' => 'web','marketplace']);

        // Assign permissions to roles
        $superAdmin->givePermissionTo(['products.manage', 'products.approve', 'attributes.manage', 'categories.manage']);
        $admin->givePermissionTo(['products.approve', 'attributes.manage', 'categories.manage']);
        $vendorRole->givePermissionTo('products.manage');
    }
}
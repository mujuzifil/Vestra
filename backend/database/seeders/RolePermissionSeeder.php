<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            // Administration
            'manage administrators' => 'Administration',
            'view audit logs' => 'Administration',

            // Customers
            'manage customers' => 'Customers',

            // Product & inventory
            'manage products' => 'Products',
            'manage inventory' => 'Products',

            // Orders
            'manage orders' => 'Orders',

            // Reports
            'view reports' => 'Reports',

            // Settings
            'manage settings' => 'Settings',

            // Notifications
            'manage notifications' => 'Notifications',
        ];

        foreach ($permissions as $name => $group) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                ['group' => $group]
            );

            if ($permission->group !== $group) {
                $permission->group = $group;
                $permission->save();
            }
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Administrator',
            'guard_name' => $guard,
        ], [
            'description' => 'Full access to the VESTRA Administration Platform.',
        ]);
        $superAdmin->syncPermissions(Permission::all());

        $administrator = Role::firstOrCreate([
            'name' => 'Administrator',
            'guard_name' => $guard,
        ], [
            'description' => 'Day-to-day administration of products, orders, customers, and reports.',
        ]);
        $administrator->syncPermissions([
            'manage products',
            'manage orders',
            'manage customers',
            'view reports',
        ]);

        Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => $guard,
        ], [
            'description' => 'Manager role reserved for future operational permissions.',
        ]);

        Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => $guard,
        ], [
            'description' => 'Default customer role for storefront users.',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            // Legacy coarse permissions retained for backward compatibility.
            'manage administrators' => 'Administration',
            'view audit logs' => 'Administration',
            'manage customers' => 'Customers',
            'manage products' => 'Products',
            'manage inventory' => 'Products',
            'manage orders' => 'Orders',
            'view reports' => 'Reports',
            'manage settings' => 'Settings',
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

        $definitions = [
            'Super Administrator' => 'Full access to the VESTRA Administration Platform.',
            'Administrator' => 'Day-to-day administration of products, orders, customers, and reports.',
            'Manager' => 'Manager role reserved for future operational permissions.',
            'customer' => 'Storefront customer role.',
        ];

        foreach ($definitions as $name => $description) {
            $role = Role::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                [
                    'description' => $description,
                    'slug' => Str::slug($name),
                    'status' => 'active',
                ]
            );

            if (! filled($role->slug)) {
                $role->slug = Str::slug($name);
            }
            if (! filled($role->status)) {
                $role->status = 'active';
            }
            if (! filled($role->description)) {
                $role->description = $description;
            }
            $role->save();
        }

        $superAdmin = Role::query()->where('name', 'Super Administrator')->firstOrFail();
        $superAdmin->syncPermissions(Permission::all());

        $administrator = Role::query()->where('name', 'Administrator')->firstOrFail();
        $administrator->syncPermissions([
            'manage products',
            'manage orders',
            'manage customers',
            'view reports',
        ]);
    }
}

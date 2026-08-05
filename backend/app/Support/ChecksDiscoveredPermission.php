<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;

trait ChecksDiscoveredPermission
{
    protected function allowsPermission(User $user, string $permission): bool
    {
        if ($user->hasRole('Super Administrator') || $user->hasRole('super-admin')) {
            return true;
        }

        // Legacy / bootstrap admins without an assigned role retain full admin access.
        if ($user->isAdmin() && $user->roles()->count() === 0) {
            return true;
        }

        if (! Permission::query()->where('name', $permission)->exists()) {
            return $user->isAdmin();
        }

        return $user->can($permission);
    }
}

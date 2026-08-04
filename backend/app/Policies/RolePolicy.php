<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Admin\RoleAdminService;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Role $role): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if (in_array($role->name, RoleAdminService::SYSTEM_ROLE_NAMES, true)) {
            return false;
        }

        return $role->users()->count() === 0;
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}

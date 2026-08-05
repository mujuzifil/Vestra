<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Services\Admin\RoleAdminService;
use App\Support\ChecksDiscoveredPermission;

class RolePolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->allowsPermission($user, 'roles.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsPermission($user, 'roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->allowsPermission($user, 'roles.edit');
    }

    public function delete(User $user, Role $role): bool
    {
        if (! $this->allowsPermission($user, 'roles.delete')) {
            return false;
        }

        if (RoleAdminService::isSystemRole($role)) {
            return false;
        }

        return $role->users()->count() === 0;
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'roles.export');
    }
}

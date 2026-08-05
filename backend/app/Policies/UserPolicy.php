<?php

namespace App\Policies;

use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class UserPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'staff.view');
    }

    public function view(User $user, User $model): bool
    {
        return $this->allowsPermission($user, 'staff.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsPermission($user, 'staff.create');
    }

    public function update(User $user, User $model): bool
    {
        return $this->allowsPermission($user, 'staff.edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $this->allowsPermission($user, 'staff.delete') && $user->id !== $model->id;
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'staff.export');
    }
}

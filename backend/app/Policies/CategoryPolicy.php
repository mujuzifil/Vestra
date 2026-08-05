<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class CategoryPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $this->allowsPermission($user, 'categories.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsPermission($user, 'categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $this->allowsPermission($user, 'categories.edit');
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->allowsPermission($user, 'categories.delete');
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'categories.export');
    }
}

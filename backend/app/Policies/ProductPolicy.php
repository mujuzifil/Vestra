<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class ProductPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->allowsPermission($user, 'products.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsPermission($user, 'products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->allowsPermission($user, 'products.edit');
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->allowsPermission($user, 'products.delete');
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'products.export');
    }
}

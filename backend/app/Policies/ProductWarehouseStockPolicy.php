<?php

namespace App\Policies;

use App\Models\ProductWarehouseStock;
use App\Models\User;

class ProductWarehouseStockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, ProductWarehouseStock $stock): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ProductWarehouseStock $stock): bool
    {
        return $user->isAdmin();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}

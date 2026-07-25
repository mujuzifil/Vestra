<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($order->distributor_id && $user->distributor?->id === $order->distributor_id) {
            return true;
        }

        return $user->id === $order->user_id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}

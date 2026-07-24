<?php

namespace App\Policies;

use App\Models\NotificationTemplate;
use App\Models\User;

class NotificationTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, NotificationTemplate $template): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, NotificationTemplate $template): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, NotificationTemplate $template): bool
    {
        return $user->isAdmin();
    }
}

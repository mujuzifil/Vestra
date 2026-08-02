<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function complete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }
}

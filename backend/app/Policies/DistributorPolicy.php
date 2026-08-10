<?php

namespace App\Policies;

use App\Models\Distributor;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class DistributorPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'active-partners.view')
            || $this->allowsPermission($user, 'distributors.view');
    }

    public function view(User $user, Distributor $distributor): bool
    {
        if ($user->id === $distributor->user_id || $user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'active-partners.view')
            || $this->allowsPermission($user, 'distributors.view');
    }

    public function update(User $user, Distributor $distributor): bool
    {
        if ($user->id === $distributor->user_id || $user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'active-partners.edit')
            || $this->allowsPermission($user, 'distributors.edit');
    }

    public function suspend(User $user, Distributor $distributor): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'active-partners.edit')
            || $this->allowsPermission($user, 'distributors.suspend');
    }

    public function manage(User $user, Distributor $distributor): bool
    {
        return $this->update($user, $distributor);
    }

    public function delete(User $user, Distributor $distributor): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'active-partners.delete')
            || $this->allowsPermission($user, 'active-partners.edit')
            || $this->allowsPermission($user, 'distributors.delete');
    }

    public function export(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'active-partners.export')
            || $this->allowsPermission($user, 'distributors.export');
    }
}

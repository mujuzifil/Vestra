<?php

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class MediaAssetPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'media.view');
    }

    public function view(User $user, MediaAsset $mediaAsset): bool
    {
        return $this->allowsPermission($user, 'media.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsPermission($user, 'media.create');
    }

    public function update(User $user, MediaAsset $mediaAsset): bool
    {
        return $this->allowsPermission($user, 'media.edit');
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return $this->allowsPermission($user, 'media.delete');
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'media.export');
    }
}

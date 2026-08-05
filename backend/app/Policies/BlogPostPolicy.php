<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class BlogPostPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'blog.view');
    }

    public function view(User $user, BlogPost $blogPost): bool
    {
        return $this->allowsPermission($user, 'blog.view');
    }

    public function create(User $user): bool
    {
        return $this->allowsPermission($user, 'blog.create');
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        return $this->allowsPermission($user, 'blog.edit');
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        return $this->allowsPermission($user, 'blog.delete');
    }

    public function publish(User $user, ?BlogPost $blogPost = null): bool
    {
        return $this->allowsPermission($user, 'blog.publish');
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'blog.export');
    }
}

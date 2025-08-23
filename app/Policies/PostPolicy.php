<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'post.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // Check if user has general view permission
        if ($this->checkPermission($user, 'post.view')) {
            return true;
        }

        // Check if user can view their own posts
        if ($this->checkPermission($user, 'post.view_own') && $this->userOwnsResource($user, $post)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'post.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Check if user has general edit permission
        if ($this->checkPermission($user, 'post.edit')) {
            return true;
        }

        // Check if user can edit their own posts
        if ($this->checkPermission($user, 'post.edit_own') && $this->userOwnsResource($user, $post)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Check if user has general delete permission
        if ($this->checkPermission($user, 'post.delete')) {
            return true;
        }

        // Check if user can delete their own posts
        if ($this->checkPermission($user, 'post.delete_own') && $this->userOwnsResource($user, $post)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'post.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'post.force_delete');
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->checkPermission($user, 'post.delete');
    }

    /**
     * Determine whether the user can publish the post.
     */
    public function publish(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'post.publish');
    }

    /**
     * Determine whether the user can manage AI content.
     */
    public function aiContent(User $user): bool
    {
        return $this->checkPermission($user, 'ai_content.generate');
    }
}

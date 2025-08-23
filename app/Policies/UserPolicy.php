<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'user.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // User can view his own profile.
        if ($user->id === $model->id) {
            return true;
        }

        return $this->checkPermission($user, 'user.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'user.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Prevent modification of super admin in demo mode
        if (! $this->canModifySuperAdmin($model)) {
            return false;
        }

        return $this->checkPermission($user, 'user.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        // Prevent deletion of super admin in demo mode
        if (! $this->canModifySuperAdmin($model)) {
            return false;
        }

        return $this->checkPermission($user, 'user.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $this->checkPermission($user, 'user.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->checkPermission($user, 'user.force_delete');
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->checkPermission($user, 'user.delete');
    }

    /**
     * Determine whether the user can view the dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $this->checkPermission($user, 'dashboard.view');
    }

    /**
     * Determine whether the user can login as another user.
     */
    public function loginAs(User $user, User $model): bool
    {
        return $this->checkPermission($user, 'user.login_as');
    }

    /**
     * Check if super admin can be modified based on demo mode.
     */
    private function canModifySuperAdmin(User $model): bool
    {
        $isSuperAdmin = $model->email === 'superadmin@example.com' ||
            $model->username === 'Superadmin' ||
            $model->hasRole('Superadmin');

        if (config('app.demo_mode') && $isSuperAdmin) {
            return false;
        }

        return true;
    }
}

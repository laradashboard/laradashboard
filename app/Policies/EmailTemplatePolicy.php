<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailTemplate;
use App\Models\User;

/**
 * Policy for the core email_templates resource.
 *
 * Uses the granular `email_template.*` permissions (which already ship
 * with the platform permission set) instead of the coarse `settings.*`
 * bucket — so a user who should manage templates does not also get
 * access to `/admin/settings` (site name, logo, etc.).
 */
class EmailTemplatePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'email_template.view');
    }

    public function view(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->checkPermission($user, 'email_template.view');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'email_template.create');
    }

    public function update(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->checkPermission($user, 'email_template.edit');
    }

    public function delete(User $user, EmailTemplate $emailTemplate): bool
    {
        return $this->checkPermission($user, 'email_template.delete');
    }
}

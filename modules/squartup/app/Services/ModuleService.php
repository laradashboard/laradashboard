<?php

declare(strict_types=1);

namespace Modules\Squartup\Services;

class ModuleService
{
    /**
     * Phase 1: do not register admin menus or permission groups.
     * Laradashboard continues to own /admin, users, roles, and permissions.
     */
    public function bootstrap(): void
    {
        // Intentionally empty until a later admin-migration phase.
    }
}

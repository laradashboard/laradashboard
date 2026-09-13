<?php

declare(strict_types=1);

namespace Modules\Squartup\Services;

class MenuService
{
    /**
     * Phase 1: do not add a sidebar item or call admin.squartup.* routes.
     *
     * @param  array<int|string, mixed>  $groups
     * @return array<int|string, mixed>
     */
    public function addMenu(array $groups): array
    {
        return $groups;
    }
}

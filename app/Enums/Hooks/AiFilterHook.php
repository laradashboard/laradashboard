<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum AiFilterHook: string
{
    /**
     * Filter to register AI action UI metadata (titles, icons, prompts, icon classes).
     *
     * Modules should add their action metadata to the array:
     * [
     *     'action.name' => [
     *         'title' => 'Human Title',
     *         'icon' => 'lucide:icon-name',
     *         'icon_class' => 'bg-blue-100 text-blue-600 ...',
     *         'prompt' => 'Pre-fill prompt text',
     *     ],
     * ]
     */
    case AI_ACTION_META = 'filter.ai_action_meta';
}

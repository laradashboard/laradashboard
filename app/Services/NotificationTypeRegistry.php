<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationType;

class NotificationTypeRegistry extends BaseTypeRegistry
{
    /**
     * Return the hook filter name to apply when calling all(). Override in subclasses.
     *
     * @return string
     */
    protected static function getFilterName(): string
    {
        return 'notification_type_values';
    }

    /**
     * Return all registered types, running an enum registration pass if the registry
     * is empty to ensure base enum values are available.
     *
     * @return string[]
     */
    public static function all(): array
    {
        $values = parent::all();
        if (empty($values)) {
            // Ensure base enum values are registered if not already.
            NotificationType::getValues();
            $values = parent::all();
        }
        return $values;
    }
}

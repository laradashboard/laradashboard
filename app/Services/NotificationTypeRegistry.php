<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationType;

class NotificationTypeRegistry extends BaseTypeRegistry
{
    protected static ?string $enumClass = NotificationType::class;

    protected static function getFilterName(): string
    {
        return 'notification_type_values';
    }
}

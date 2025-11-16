<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Facades\Hook;

class NotificationType
{
    public const FORGOT_PASSWORD = 'forgot_password';
    public const CUSTOM = 'custom';

    public static function getValues(): array
    {
        return Hook::applyFilters('notification_type_values', [
            self::FORGOT_PASSWORD,
            self::CUSTOM,
        ]);
    }
}
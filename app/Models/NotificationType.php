<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Facades\Hook;

class NotificationType
{
    public const FORGOT_PASSWORD = 'forgot_password';
    public const ACTIVITY_CREATED = 'activity_created';
    public const ACTIVITY_UPDATED = 'activity_updated';
    public const ACTIVITY_DELETED = 'activity_deleted';
    public const CUSTOM = 'custom';

    public static function getValues(): array
    {
        return Hook::applyFilters('notification_type_values', [
            self::FORGOT_PASSWORD,
            self::ACTIVITY_CREATED,
            self::ACTIVITY_UPDATED,
            self::ACTIVITY_DELETED,
            self::CUSTOM,
        ]);
    }

    public function label($value): string
    {
        return match ($value) {
            self::FORGOT_PASSWORD => __('Forgot Password'),
            self::ACTIVITY_CREATED => __('Activity Created'),
            self::ACTIVITY_UPDATED => __('Activity Updated'),
            self::ACTIVITY_DELETED => __('Activity Deleted'),
            self::CUSTOM => __('Custom'),
            default => __('Unknown'),
        };
    }

    public function icon($value): string
    {
        return match ($value) {
            self::FORGOT_PASSWORD => 'lucide:key',
            self::ACTIVITY_CREATED => 'lucide:plus-circle',
            self::ACTIVITY_UPDATED => 'lucide:edit',
            self::ACTIVITY_DELETED => 'lucide:trash-2',
            self::CUSTOM => 'lucide:bell',
            default => 'lucide:alert-circle',
        };
    }
}

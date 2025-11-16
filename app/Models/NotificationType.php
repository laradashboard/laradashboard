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

    public function label($value): string
    {
        return match ($value) {
            self::FORGOT_PASSWORD => __('Forgot Password'),
            self::CUSTOM => __('Custom'),
            default => __('Unknown'),
        };
    }

    public function icon($value): string
    {
        return match ($value) {
            self::FORGOT_PASSWORD => 'lucide:key',
            self::CUSTOM => 'lucide:bell',
            default => 'lucide:alert-circle',
        };
    }
}
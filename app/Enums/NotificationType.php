<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\NotificationTypeRegistry;

enum NotificationType: string
{
    case FORGOT_PASSWORD = 'forgot_password';
    case CUSTOM = 'custom';

    public const FORGOT_PASSWORD_VALUE = 'forgot_password';
    public const CUSTOM_VALUE = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::FORGOT_PASSWORD => __('Forgot Password'),
            self::CUSTOM => __('Custom'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FORGOT_PASSWORD => 'lucide:key',
            self::CUSTOM => 'lucide:bell',
        };
    }

    public static function getValues(): array
    {
        // register base types with the registry
        NotificationTypeRegistry::registerMany([
            ['type' => self::FORGOT_PASSWORD->value, 'meta' => ['label' => fn () => __('Forgot Password'), 'icon' => fn () => 'lucide:key']],
            ['type' => self::CUSTOM->value, 'meta' => ['label' => fn () => __('Custom'), 'icon' => fn () => 'lucide:bell']],
        ]);
        return NotificationTypeRegistry::all();
    }
}

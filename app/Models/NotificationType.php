<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\NotificationTypeRegistry;

// Keep label and icon logic in the model; Hook filters are applied at registry level.

class NotificationType
{
    public const FORGOT_PASSWORD = 'forgot_password';
    public const CUSTOM = 'custom';

    public static function getValues(): array
    {
        self::registerBaseTypes();
        return NotificationTypeRegistry::all();
    }

    protected static bool $areBaseTypesRegistered = false;

    protected static function registerBaseTypes(): void
    {
        if (static::$areBaseTypesRegistered) {
            return;
        }
        NotificationTypeRegistry::registerMany([
            ['type' => self::FORGOT_PASSWORD, 'meta' => ['label' => fn () => __('Forgot Password'), 'icon' => 'lucide:key']],
            ['type' => self::CUSTOM, 'meta' => ['label' => fn () => __('Custom'), 'icon' => 'lucide:bell']],
        ]);
        static::$areBaseTypesRegistered = true;
    }

    public function label($value): string
    {
        $label = NotificationTypeRegistry::getLabel($value);
        if ($label) {
            return (string) $label;
        }
        return match ($value) {
            self::FORGOT_PASSWORD => __('Forgot Password'),
            self::CUSTOM => __('Custom'),
            default => __('Unknown'),
        };
    }

    public function icon($value): string
    {
        $icon = NotificationTypeRegistry::getIcon($value);
        if ($icon) {
            return $icon;
        }
        return match ($value) {
            self::FORGOT_PASSWORD => 'lucide:key',
            self::CUSTOM => 'lucide:bell',
            default => 'lucide:alert-circle',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\NotificationTypeRegistry;

enum NotificationType: string
{
    case FORGOT_PASSWORD = 'forgot_password';
    case REGISTRATION_WELCOME = 'registration_welcome';
    case EMAIL_VERIFICATION = 'email_verification';
    case CUSTOM = 'custom';

    public const FORGOT_PASSWORD_VALUE = 'forgot_password';
    public const REGISTRATION_WELCOME_VALUE = 'registration_welcome';
    public const EMAIL_VERIFICATION_VALUE = 'email_verification';
    public const CUSTOM_VALUE = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::FORGOT_PASSWORD => __('Forgot Password'),
            self::REGISTRATION_WELCOME => __('Registration Welcome'),
            self::EMAIL_VERIFICATION => __('Email Verification'),
            self::CUSTOM => __('Custom'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FORGOT_PASSWORD => 'lucide:key',
            self::REGISTRATION_WELCOME => 'lucide:user-plus',
            self::EMAIL_VERIFICATION => 'lucide:mail-check',
            self::CUSTOM => 'lucide:bell',
        };
    }

    public static function getValues(): array
    {
        // register base types with the registry
        NotificationTypeRegistry::registerMany([
            ['type' => self::FORGOT_PASSWORD->value, 'meta' => ['label' => fn () => __('Forgot Password'), 'icon' => fn () => 'lucide:key']],
            ['type' => self::REGISTRATION_WELCOME->value, 'meta' => ['label' => fn () => __('Registration Welcome'), 'icon' => fn () => 'lucide:user-plus']],
            ['type' => self::EMAIL_VERIFICATION->value, 'meta' => ['label' => fn () => __('Email Verification'), 'icon' => fn () => 'lucide:mail-check']],
            ['type' => self::CUSTOM->value, 'meta' => ['label' => fn () => __('Custom'), 'icon' => fn () => 'lucide:bell']],
        ]);
        return NotificationTypeRegistry::all();
    }
}

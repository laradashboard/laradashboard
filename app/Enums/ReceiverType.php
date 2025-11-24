<?php

declare(strict_types=1);

namespace App\Enums;

enum ReceiverType: string
{
    case USER = 'user';
    case ANY_EMAIL = 'any_email';

    public function label(): string
    {
        return match($this) {
            self::USER => 'User',
            self::ANY_EMAIL => 'Any Email',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::USER => 'Send to registered users',
            self::ANY_EMAIL => 'Send to any email address',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}

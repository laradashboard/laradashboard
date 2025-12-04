<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\ReceiverTypeRegistry;

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
        // Ensure base enum values are registered in the registry, then return all values from the registry
        ReceiverTypeRegistry::registerMany(array_map(fn ($c) => ['type' => $c->value, 'meta' => ['label' => fn () => $c->label(), 'description' => fn () => $c->description()]], self::cases()));
        return ReceiverTypeRegistry::all();
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReceiverType;

class ReceiverTypeRegistry extends BaseTypeRegistry
{
    protected static ?string $enumClass = ReceiverType::class;

    protected static function getFilterName(): string
    {
        return 'receiver_type_values';
    }
}

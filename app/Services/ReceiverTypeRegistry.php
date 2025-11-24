<?php

declare(strict_types=1);

namespace App\Services;

class ReceiverTypeRegistry extends BaseTypeRegistry
{
    protected static function getFilterName(): string
    {
        return 'receiver_type_values';
    }

    public static function all(): array
    {
        $values = parent::all();
        if (empty($values)) {
            // Ensure base enum values are registered if not already.
            \App\Enums\ReceiverType::getValues();
            $values = parent::all();
        }
        return $values;
    }
}

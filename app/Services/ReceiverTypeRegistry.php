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

    public static function getDropdownItems(): array
    {
        return collect(static::all())
            ->mapWithKeys(function ($type) {
                $label = static::getLabel($type) ?: (\App\Enums\ReceiverType::tryFrom($type)?->label() ?? ucfirst(str_replace('_', ' ', $type)));
                return [$type => $label];
            })
            ->toArray();
    }
}

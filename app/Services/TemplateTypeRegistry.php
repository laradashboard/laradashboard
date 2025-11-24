<?php

declare(strict_types=1);

namespace App\Services;

class TemplateTypeRegistry extends BaseTypeRegistry
{
    protected static function getFilterName(): string
    {
        return 'template_type_values';
    }

    public static function all(): array
    {
        $values = parent::all();
        if (empty($values)) {
            \App\Enums\TemplateType::getValues();
            $values = parent::all();
        }
        return $values;
    }
}

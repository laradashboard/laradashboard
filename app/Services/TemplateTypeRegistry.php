<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TemplateType;

class TemplateTypeRegistry extends BaseTypeRegistry
{
    protected static ?string $enumClass = TemplateType::class;

    protected static function getFilterName(): string
    {
        return 'template_type_values';
    }
}

<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class HiddenInput extends BaseFieldBuilder
{
    protected static function getFieldType(): string
    {
        return 'hidden';
    }
}

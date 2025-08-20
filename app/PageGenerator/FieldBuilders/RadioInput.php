<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class RadioInput extends BaseFieldBuilder
{
    protected static function getFieldType(): string
    {
        return 'radio';
    }

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'options' => $this->options,
        ];
    }
}

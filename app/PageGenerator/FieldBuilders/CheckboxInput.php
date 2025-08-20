<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class CheckboxInput extends BaseFieldBuilder
{
    protected mixed $checkedValue = 1;
    protected mixed $uncheckedValue = 0;

    protected static function getFieldType(): string
    {
        return 'checkbox';
    }

    public function checkedValue(mixed $value): static
    {
        $this->checkedValue = $value;
        return $this;
    }

    public function uncheckedValue(mixed $value): static
    {
        $this->uncheckedValue = $value;
        return $this;
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'checkedValue' => $this->checkedValue,
            'uncheckedValue' => $this->uncheckedValue,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class SelectInput extends BaseFieldBuilder
{
    protected bool $multiple = false;
    protected ?string $emptyOption = null;
    protected bool $searchable = false;

    protected static function getFieldType(): string
    {
        return 'select';
    }

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function emptyOption(string $emptyOption): static
    {
        $this->emptyOption = $emptyOption;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'options' => $this->options,
            'multiple' => $this->multiple,
            'emptyOption' => $this->emptyOption,
            'searchable' => $this->searchable,
        ];
    }
}

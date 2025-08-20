<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class TextInput extends BaseFieldBuilder
{
    protected ?int $minLength = null;
    protected ?int $maxLength = null;
    protected ?string $pattern = null;

    protected static function getFieldType(): string
    {
        return 'text';
    }

    public function minLength(int $minLength): static
    {
        $this->minLength = $minLength;
        return $this;
    }

    public function maxLength(int $maxLength): static
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function pattern(string $pattern): static
    {
        $this->pattern = $pattern;
        return $this;
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'minLength' => $this->minLength,
            'maxLength' => $this->maxLength,
            'pattern' => $this->pattern,
        ];
    }
}

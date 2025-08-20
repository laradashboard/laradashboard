<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class FileInput extends BaseFieldBuilder
{
    protected ?string $accept = null;
    protected bool $multiple = false;
    protected ?int $maxSize = null;

    protected static function getFieldType(): string
    {
        return 'file';
    }

    public function accept(string $accept): static
    {
        $this->accept = $accept;
        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function maxSize(int $maxSize): static
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    public function images(): static
    {
        return $this->accept('image/*');
    }

    public function documents(): static
    {
        return $this->accept('.pdf,.doc,.docx,.txt');
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'accept' => $this->accept,
            'multiple' => $this->multiple,
            'maxSize' => $this->maxSize,
        ];
    }
}

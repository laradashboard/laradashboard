<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class TextareaInput extends BaseFieldBuilder
{
    protected ?int $rows = null;
    protected ?int $cols = null;
    protected bool $resizable = true;

    protected static function getFieldType(): string
    {
        return 'textarea';
    }

    public function rows(int $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function cols(int $cols): static
    {
        $this->cols = $cols;
        return $this;
    }

    public function resizable(bool $resizable = true): static
    {
        $this->resizable = $resizable;
        return $this;
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'rows' => $this->rows,
            'cols' => $this->cols,
            'resizable' => $this->resizable,
        ];
    }
}

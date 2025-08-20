<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class PasswordInput extends BaseFieldBuilder
{
    protected bool $showAutoGenerate = false;
    protected ?int $minLength = null;

    protected static function getFieldType(): string
    {
        return 'password';
    }

    public function showAutoGenerate(bool $show = true): static
    {
        $this->showAutoGenerate = $show;
        return $this;
    }

    public function minLength(int $minLength): static
    {
        $this->minLength = $minLength;
        return $this;
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'showAutoGenerate' => $this->showAutoGenerate,
            'minLength' => $this->minLength,
        ];
    }
}

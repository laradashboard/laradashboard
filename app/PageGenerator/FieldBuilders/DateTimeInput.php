<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

class DateTimeInput extends BaseFieldBuilder
{
    protected bool $enableTime = true;
    protected ?string $dateFormat = null;
    protected ?string $timeFormat = null;
    protected ?string $minDate = null;
    protected ?string $maxDate = null;

    protected static function getFieldType(): string
    {
        return 'datetime-picker';
    }

    public function enableTime(bool $enableTime = true): static
    {
        $this->enableTime = $enableTime;
        return $this;
    }

    public function dateOnly(): static
    {
        return $this->enableTime(false);
    }

    public function dateFormat(string $format): static
    {
        $this->dateFormat = $format;
        return $this;
    }

    public function timeFormat(string $format): static
    {
        $this->timeFormat = $format;
        return $this;
    }

    public function minDate(string $minDate): static
    {
        $this->minDate = $minDate;
        return $this;
    }

    public function maxDate(string $maxDate): static
    {
        $this->maxDate = $maxDate;
        return $this;
    }

    protected function getAdditionalProperties(): array
    {
        return [
            'enableTime' => $this->enableTime,
            'dateFormat' => $this->dateFormat,
            'timeFormat' => $this->timeFormat,
            'minDate' => $this->minDate,
            'maxDate' => $this->maxDate,
        ];
    }
}

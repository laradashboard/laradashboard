<?php

declare(strict_types=1);

namespace App\PageGenerator;

use App\PageGenerator\FieldBuilders\BaseFieldBuilder;

class FormBuilder
{
    protected array $fields = [];
    protected array $sections = [];
    protected ?string $defaultContainerClass = null;
    protected bool $useSections = true;

    public function __construct(bool $useSections = true)
    {
        $this->useSections = $useSections;
    }

    public static function make(bool $useSections = true): self
    {
        return new self($useSections);
    }

    public function field(string $name, BaseFieldBuilder|array $field): static
    {
        if ($field instanceof BaseFieldBuilder) {
            $this->fields[$name] = $field->toArray();
        } else {
            $this->fields[$name] = $field;
        }

        return $this;
    }

    public function add(BaseFieldBuilder $field): static
    {
        $this->fields[$field->getName()] = $field->toArray();
        return $this;
    }

    public function fields(array $fields): static
    {
        foreach ($fields as $name => $field) {
            $this->field($name, $field);
        }

        return $this;
    }

    public function section(string $title, array $fields, ?string $description = null, ?string $columns = null): static
    {
        $this->sections[] = [
            'title' => $title,
            'description' => $description,
            'fields' => $fields,
            'columns' => $columns,
        ];

        return $this;
    }

    public function defaultContainerClass(string $class): static
    {
        $this->defaultContainerClass = $class;
        return $this;
    }

    public function getFields(): array
    {
        // Apply default container class if set and field doesn't have one
        $fields = $this->fields;

        if ($this->defaultContainerClass) {
            foreach ($fields as $name => &$field) {
                if (! isset($field['containerClass']) || empty($field['containerClass'])) {
                    $field['containerClass'] = $this->defaultContainerClass;
                }
            }
        }

        return $fields;
    }

    public function getSections(): array
    {
        if (! $this->useSections) {
            return [];
        }

        return $this->sections;
    }

    public function getUseSections(): bool
    {
        return $this->useSections;
    }

    public function toArray(): array
    {
        return [
            'fields' => $this->getFields(),
            'sections' => $this->getSections(),
            'useSections' => $this->getUseSections(),
        ];
    }
}

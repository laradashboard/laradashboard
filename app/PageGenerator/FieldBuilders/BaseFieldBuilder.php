<?php

declare(strict_types=1);

namespace App\PageGenerator\FieldBuilders;

abstract class BaseFieldBuilder
{
    protected string $name;
    protected string $type;
    protected ?string $label = null;
    protected ?string $placeholder = null;
    protected ?string $help = null;
    protected mixed $value = null;
    protected mixed $default = null;
    protected bool $required = false;
    protected bool $disabled = false;
    protected bool $readonly = false;
    protected array $attributes = [];
    protected array $rules = [];
    protected ?string $containerClass = null;
    protected ?string $inputClass = null;
    protected array $options = [];

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function make(string $name): static
    {
        return new static($name, static::getFieldType());
    }

    public static function __callStatic(string $method, array $arguments): static
    {
        // Allow direct method calls like TextInput::required('name')
        if (count($arguments) === 1 && is_string($arguments[0])) {
            $instance = static::make($arguments[0]);

            // Call the method if it exists
            if (method_exists($instance, $method)) {
                return $instance->$method();
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist on " . static::class);
    }

    abstract protected static function getFieldType(): string;

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function help(string $help): static
    {
        $this->help = $help;
        return $this;
    }

    public function value(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;
        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;
        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function readonly(bool $readonly = true): static
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function attribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function rules(array|string $rules): static
    {
        $this->rules = is_array($rules) ? $rules : [$rules];
        return $this;
    }

    public function containerClass(string $class): static
    {
        $this->containerClass = $class;
        return $this;
    }

    public function inputClass(string $class): static
    {
        $this->inputClass = $class;
        return $this;
    }

    public function columns(string $columns): static
    {
        return $this->containerClass($columns);
    }

    public function fullWidth(): static
    {
        return $this->containerClass('col-span-full');
    }

    public function halfWidth(): static
    {
        return $this->containerClass('md:col-span-1');
    }

    public function toArray(): array
    {
        $field = [
            'type' => $this->type,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'help' => $this->help,
            'value' => $this->value,
            'default' => $this->default,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'readonly' => $this->readonly,
            'attributes' => $this->attributes,
            'rules' => $this->rules,
            'containerClass' => $this->containerClass,
            'inputClass' => $this->inputClass,
        ];

        // Add any additional field-specific properties
        $field = array_merge($field, $this->getAdditionalProperties());

        // Remove null values to keep array clean
        return array_filter($field, fn ($value) => $value !== null);
    }

    protected function getAdditionalProperties(): array
    {
        return [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTypeValue(): string
    {
        return $this->type;
    }
}

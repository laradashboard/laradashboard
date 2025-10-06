<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class StatusChangeButton extends Component
{
    public Model $model;
    public array $options;
    public string $eventName = 'status-updated';
    public string $actionField = 'status';
    public string $fieldType = 'string';

    public function mount(
        Model $model,
        array $options,
        ?string $eventName = null,
        ?string $actionField = null
    ) {
        $this->model = $model;
        $this->actionField = $actionField ?? $this->actionField;
        $this->options = $options;
        $this->eventName = $eventName ?? $this->eventName;
        $this->fieldType = $this->determineFieldType($this->model->{$this->actionField});
    }

    private function determineFieldType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        return 'string';
    }

    private function castValue($value)
    {
        switch ($this->fieldType) {
            case 'boolean':
                if ($value === 'true' || $value === '1') {
                    return true;
                } elseif ($value === 'false' || $value === '0') {
                    return false;
                }
                if (is_numeric($value)) {
                    return (bool)$value;
                }
                return (bool)$value;

            case 'integer':
                return (int)$value;

            default:
                return (string)$value;
        }
    }

    public function changeStatusTo($newStatus): void
    {
        $newStatus = $this->castValue($newStatus);
        $this->model->update([$this->actionField => $newStatus]);
        $this->model->refresh();
        $this->dispatch($this->eventName, $this->model->id);
    }

    public function render()
    {
        return view('components.livewire.status-change-button');
    }
}

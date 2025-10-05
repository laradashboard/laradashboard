<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class StatusChangeButton extends Component
{
    public Model $model;
    public $status;
    public array $statuses;
    public string $eventName = 'status-updated';
    public string $statusField = 'status';
    public string $fieldType = 'string';

    public function mount(
        Model $model, 
        array $statuses, 
        ?string $eventName = null,
        ?string $statusField = null
    ) {
        $this->model = $model;
        $this->statusField = $statusField ?? $this->statusField;
        $this->status = $model->{$this->statusField};
        $this->statuses = $statuses;
        $this->eventName = $eventName ?? $this->eventName;
        
        // Determine the field type automatically based on the current value
        $this->fieldType = $this->determineFieldType($this->status);
    }

    /**
     * Determine the data type of the status field
     */
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

    /**
     * Cast new status value to the appropriate type
     */
    private function castValue($value)
    {
        // Handle different field types
        switch ($this->fieldType) {
            case 'boolean':
                // Convert strings 'true'/'false' to boolean
                if ($value === 'true' || $value === '1') {
                    return true;
                } elseif ($value === 'false' || $value === '0') {
                    return false;
                }
                // Convert numeric values to boolean
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
        
        $this->status = $newStatus;
        $this->model->update([$this->statusField => $newStatus]);
        $this->model->refresh();
        $this->dispatch($this->eventName, $this->model->id);
    }

    public function render()
    {
        return view('components.livewire.status-change-button');
    }
}

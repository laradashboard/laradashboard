@props([
    'field',
    'name',
    'model' => null,
])

@php
    $type = $field['type'] ?? 'text';
    $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $name));
    $placeholder = $field['placeholder'] ?? '';
    $required = $field['required'] ?? false;
    $disabled = $field['disabled'] ?? false;
    $readonly = $field['readonly'] ?? false;
    $help = $field['help'] ?? '';
    $value = old($name, $model ? $model->$name : ($field['default'] ?? ''));
    $options = $field['options'] ?? [];
    $multiple = $field['multiple'] ?? false;
    $rows = $field['rows'] ?? 3;
    $cols = $field['cols'] ?? null;
    $containerClass = $field['containerClass'] ?? '';
    $inputClass = $field['class'] ?? '';
@endphp

@switch($type)
    @case('password')
        <div class="{{ $containerClass }}">
            <x-inputs.password
                :name="$name"
                :label="$label"
                :placeholder="$placeholder"
                :value="$value"
                :required="$required"
                :class="$inputClass"
                :show-auto-generate="$field['showAutoGenerate'] ?? false"
                :autocomplete="$field['autocomplete'] ?? 'new-password'"
            />
            
            @if($help)
                <p class="form-help">{{ $help }}</p>
            @endif
            
            @error($name)
                <p class="form-error-message">{{ $message }}</p>
            @enderror
        </div>
        @break
        
    @case('text')
    @case('email')
    @case('number')
    @case('tel')
    @case('url')
        <x-inputs.text
            :name="$name"
            :type="$type"
            :label="$label"
            :value="$value"
            :placeholder="$placeholder"
            :required="$required"
            :disabled="$disabled"
            :readonly="$readonly"
            :min="$field['min'] ?? null"
            :max="$field['max'] ?? null"
            :step="$field['step'] ?? null"
            :pattern="$field['pattern'] ?? null"
            :help="$help"
            :class="$inputClass"
            :container-class="$containerClass"
        />
        @break
        
    @case('datetime')
    @case('datetime-local')
        <div class="{{ $containerClass }}">
            <x-inputs.datetime-picker
                :id="$name"
                :name="$name"
                :label="$label"
                :value="$value"
                :required="$required"
                :placeholder="$placeholder"
                :min-date="$field['minDate'] ?? null"
                :max-date="$field['maxDate'] ?? null"
                :enable-time="$field['enableTime'] ?? true"
                :date-format="$field['dateFormat'] ?? 'Y-m-d H:i'"
                :alt-format="$field['altFormat'] ?? 'F j, Y at h:i K'"
                :class="$inputClass"
                :help-text="$help"
            />
            
            @error($name)
                <p class="form-error-message">{{ $message }}</p>
            @enderror
        </div>
        @break
        
    @case('date')
        <div class="{{ $containerClass }}">
            <x-inputs.datetime-picker
                :id="$name"
                :name="$name"
                :label="$label"
                :value="$value"
                :required="$required"
                :placeholder="$placeholder"
                :min-date="$field['minDate'] ?? null"
                :max-date="$field['maxDate'] ?? null"
                :enable-time="false"
                :date-format="$field['dateFormat'] ?? 'Y-m-d'"
                :alt-format="$field['altFormat'] ?? 'F j, Y'"
                :class="$inputClass"
                :help-text="$help"
            />
            
            @error($name)
                <p class="form-error-message">{{ $message }}</p>
            @enderror
        </div>
        @break
        
    @case('time')
    @case('color')
        <x-inputs.text
            :name="$name"
            :type="$type"
            :label="$label"
            :value="$value"
            :placeholder="$placeholder"
            :required="$required"
            :disabled="$disabled"
            :readonly="$readonly"
            :min="$field['min'] ?? null"
            :max="$field['max'] ?? null"
            :step="$field['step'] ?? null"
            :pattern="$field['pattern'] ?? null"
            :help="$help"
            :class="$inputClass"
            :container-class="$containerClass"
        />
        @break
        
    @case('textarea')
        <x-inputs.textarea
            :name="$name"
            :label="$label"
            :value="$value"
            :placeholder="$placeholder"
            :required="$required"
            :disabled="$disabled"
            :readonly="$readonly"
            :rows="$rows"
            :cols="$cols"
            :help="$help"
            :class="$inputClass"
            :container-class="$containerClass"
        />
        @break
        
    @case('select')
        <x-inputs.select
            :name="$name"
            :label="$label"
            :options="$options"
            :selected="$value"
            :placeholder="$placeholder"
            :required="$required"
            :disabled="$disabled"
            :multiple="$multiple"
            :help="$help"
            :class="$inputClass"
            :container-class="$containerClass"
        />
        @break
        
    @case('checkbox')
        <x-inputs.checkbox
            :name="$name"
            :label="$label"
            :value="$field['value'] ?? '1'"
            :checked="(bool)$value"
            :disabled="$disabled"
            :required="$required"
            :help="$help"
            :class="$inputClass"
            :container-class="$containerClass"
        />
        @break
        
    @case('radio')
        <x-inputs.radio
            :name="$name"
            :label="$label"
            :options="$options"
            :selected="$value"
            :disabled="$disabled"
            :required="$required"
            :help="$help"
            :class="$inputClass"
            :container-class="$containerClass"
        />
        @break
        
    @case('file')
        <div class="{{ $containerClass }}">
            <x-inputs.file-input
                :label="$label"
                :name="$name"
                :id="$name"
                :multiple="$multiple"
                :existing-attachment="$model && $value ? $value : null"
                :existing-alt-text="$field['altText'] ?? ''"
                :remove-checkbox-name="$field['removeCheckboxName'] ?? 'remove_' . $name"
                :remove-checkbox-label="$field['removeCheckboxLabel'] ?? ($model && $value ? __('Remove current file') : null)"
                class=""
            >
                @if($help)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $help }}</p>
                @endif
            </x-inputs.file-input>
            
            @error($name)
                <p class="form-error-message">{{ $message }}</p>
            @enderror
        </div>
        @break
        
    @case('hidden')
        <x-inputs.hidden
            :name="$name"
            :value="$value"
        />
        @break
        
    @case('custom')
        <div class="{{ $containerClass }}">
            @if(!in_array($type, ['hidden', 'checkbox', 'radio']))
                <label for="{{ $name }}" class="form-label">
                    {{ $label }}
                    @if($required)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
            @endif
            
            @if(isset($field['component']))
                <x-dynamic-component :component="$field['component']" 
                                   :name="$name" 
                                   :value="$value" 
                                   :field="$field"
                                   :model="$model" />
            @elseif(isset($field['view']))
                @include($field['view'], [
                    'name' => $name,
                    'value' => $value,
                    'field' => $field,
                    'model' => $model,
                ])
            @endif
            
            @if($help)
                <p class="form-help">{{ $help }}</p>
            @endif
            
            @error($name)
                <p class="form-error-message">{{ $message }}</p>
            @enderror
        </div>
        @break
@endswitch
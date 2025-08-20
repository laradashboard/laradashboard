@props([
    'name',
    'label' => '',
    'options' => [],
    'selected' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'help' => '',
    'class' => '',
    'containerClass' => '',
])

<div class="{{ $containerClass }}">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select id="{{ $name }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($multiple) multiple @endif
            class="form-control {{ $class }}"
            {{ $attributes }}>
        
        @if(!$required && !$multiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $optionValue => $optionLabel)
            @if(is_array($optionLabel) && isset($optionLabel['label']))
                <option value="{{ $optionValue }}"
                        @if($multiple ? in_array($optionValue, (array)old($name, $selected)) : old($name, $selected) == $optionValue) selected @endif
                        @if($optionLabel['disabled'] ?? false) disabled @endif>
                    {{ $optionLabel['label'] }}
                </option>
            @else
                <option value="{{ $optionValue }}"
                        @if($multiple ? in_array($optionValue, (array)old($name, $selected)) : old($name, $selected) == $optionValue) selected @endif>
                    {{ $optionLabel }}
                </option>
            @endif
        @endforeach
    </select>
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    
    @error($name)
        <p class="form-error-message">{{ $message }}</p>
    @enderror
</div>
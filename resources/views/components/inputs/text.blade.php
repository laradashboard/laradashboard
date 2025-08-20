@props([
    'name',
    'label' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'min' => null,
    'max' => null,
    'step' => null,
    'pattern' => null,
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
    
    <input type="{{ $type }}"
           id="{{ $name }}"
           name="{{ $name }}"
           value="{{ old($name, $value) }}"
           placeholder="{{ $placeholder }}"
           @if($required) required @endif
           @if($disabled) disabled @endif
           @if($readonly) readonly @endif
           @if($min !== null) min="{{ $min }}" @endif
           @if($max !== null) max="{{ $max }}" @endif
           @if($step !== null) step="{{ $step }}" @endif
           @if($pattern !== null) pattern="{{ $pattern }}" @endif
           class="form-control {{ $class }}"
           {{ $attributes }}>
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    
    @error($name)
        <p class="form-error-message">{{ $message }}</p>
    @enderror
</div>
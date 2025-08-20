@props([
    'name',
    'label' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 3,
    'cols' => null,
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
    
    <textarea id="{{ $name }}"
              name="{{ $name }}"
              placeholder="{{ $placeholder }}"
              rows="{{ $rows }}"
              @if($cols) cols="{{ $cols }}" @endif
              @if($required) required @endif
              @if($disabled) disabled @endif
              @if($readonly) readonly @endif
              class="form-control-textarea {{ $class }}"
              {{ $attributes }}>{{ old($name, $value) }}</textarea>
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    
    @error($name)
        <p class="form-error-message">{{ $message }}</p>
    @enderror
</div>
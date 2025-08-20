@props([
    'name',
    'label' => '',
    'options' => [],
    'selected' => '',
    'disabled' => false,
    'required' => false,
    'help' => '',
    'class' => '',
    'containerClass' => '',
])

<div class="{{ $containerClass }}">
    @if($label)
        <span class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </span>
    @endif
    
    <div class="space-y-2 mt-1">
        @foreach($options as $optionValue => $optionLabel)
            <div class="flex items-center">
                <input type="radio"
                       id="{{ $name }}_{{ $optionValue }}"
                       name="{{ $name }}"
                       value="{{ $optionValue }}"
                       @if(old($name, $selected) == $optionValue) checked @endif
                       @if($disabled) disabled @endif
                       @if($required) required @endif
                       class="form-radio h-4 w-4 text-primary {{ $class }}"
                       {{ $attributes }}>
                
                <label for="{{ $name }}_{{ $optionValue }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ $optionLabel }}
                </label>
            </div>
        @endforeach
    </div>
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    
    @error($name)
        <p class="form-error-message">{{ $message }}</p>
    @enderror
</div>
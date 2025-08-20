@props([
    'name',
    'label' => '',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'required' => false,
    'help' => '',
    'class' => '',
    'containerClass' => '',
])

<div class="{{ $containerClass }}">
    <div class="flex items-center">
        <input type="checkbox"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $value }}"
               @if(old($name, $checked)) checked @endif
               @if($disabled) disabled @endif
               @if($required) required @endif
               class="form-checkbox h-4 w-4 text-primary {{ $class }}"
               {{ $attributes }}>
        
        @if($label)
            <label for="{{ $name }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
        @endif
    </div>
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    
    @error($name)
        <p class="form-error-message">{{ $message }}</p>
    @enderror
</div>
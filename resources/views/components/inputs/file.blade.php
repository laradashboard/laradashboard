@props([
    'name',
    'label' => '',
    'accept' => '',
    'multiple' => false,
    'required' => false,
    'disabled' => false,
    'help' => '',
    'preview' => false,
    'model' => null,
    'value' => '',
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
    
    <input type="file"
           id="{{ $name }}"
           name="{{ $name }}{{ $multiple ? '[]' : '' }}"
           @if($required) required @endif
           @if($disabled) disabled @endif
           @if($multiple) multiple @endif
           @if($accept) accept="{{ $accept }}" @endif
           class="form-control-file {{ $class }}"
           {{ $attributes }}>
    
    @if($preview && $model && $value)
        <div class="mt-2">
            @if(method_exists($model, 'getMimeType') && str_starts_with($model->getMimeType($name) ?? '', 'image/'))
                <img src="{{ $value }}" alt="Preview" class="max-w-xs rounded">
            @else
                <a href="{{ $value }}" target="_blank" class="text-primary hover:underline">
                    {{ __('View current file') }}
                </a>
            @endif
        </div>
    @endif
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    
    @error($name)
        <p class="form-error-message">{{ $message }}</p>
    @enderror
</div>
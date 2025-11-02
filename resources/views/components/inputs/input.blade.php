@props([
    'label' => null,
    'placeholder' => '',
    'hint' => null,
    'type' => 'text',
    'name' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'autocomplete' => null,
    'min' => null,
    'max' => null,
    'step' => null,
])
<div>
    @if($label)
        <label class="form-label" for="{{ $name }}">
            {{ $label }}

            @if($required)
                <span class="crm:text-red-500">*</span>
            @endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($min) min="{{ $min }}" @endif
        @if($max) max="{{ $max }}" @endif
        @if($step) step="{{ $step }}" @endif
        {{ $attributes->class(['form-control', 'form-input']) }}
    >
    @if($hint)
        <div class="text-xs text-gray-400 mt-1">{{ $hint }}</div>
    @endif
</div>

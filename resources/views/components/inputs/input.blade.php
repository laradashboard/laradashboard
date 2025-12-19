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
    'addonLeft' => null,
    'addonRight' => null,
    'addonLeftIcon' => null,
    'addonRightIcon' => null,
])

@php
    $hasAddonLeft = $addonLeft || $addonLeftIcon;
    $hasAddonRight = $addonRight || $addonRightIcon;
    $hasAddon = $hasAddonLeft || $hasAddonRight;

    // Determine input classes based on addons
    $inputClasses = ['form-input', 'flex-1', 'min-w-0', 'h-10', 'px-4', 'py-2.5', 'text-sm', 'leading-5', 'text-gray-800', 'dark:text-white/90', 'dark:bg-gray-900', 'placeholder:text-gray-400', 'dark:placeholder:text-white/30', 'disabled:cursor-not-allowed', 'disabled:opacity-50'];

    if ($hasAddon) {
        // Remove default ring/outline - parent handles focus state
        $inputClasses[] = 'border-0 ring-0 focus:ring-0 focus:outline-none';
        if ($hasAddonLeft && $hasAddonRight) {
            $inputClasses[] = 'rounded-none';
        } elseif ($hasAddonLeft) {
            $inputClasses[] = 'rounded-l-none rounded-r';
        } elseif ($hasAddonRight) {
            $inputClasses[] = 'rounded-r-none rounded-l';
        }
    } else {
        $inputClasses[] = 'form-control';
    }
@endphp

<div>
    @if($label)
        <label class="form-label" for="{{ $name }}">
            {{ $label }}

            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    @if($hasAddon)
        <div class="flex rounded ring-1 ring-gray-200 dark:ring-gray-700 focus-within:ring-2 focus-within:ring-primary overflow-hidden">
            {{-- Left Addon --}}
            @if($hasAddonLeft)
                <span class="inline-flex items-center justify-center px-3 h-10 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm font-medium border-r border-gray-200 dark:border-gray-700">
                    @if($addonLeftIcon)
                        <iconify-icon icon="{{ $addonLeftIcon }}" class="{{ $addonLeft ? 'mr-1.5' : '' }}"></iconify-icon>
                    @endif
                    {{ $addonLeft }}
                </span>
            @endif

            {{-- Input --}}
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                @if($min !== null) min="{{ $min }}" @endif
                @if($max !== null) max="{{ $max }}" @endif
                @if($step !== null) step="{{ $step }}" @endif
                {{ $attributes->class($inputClasses) }}
            >

            {{-- Right Addon --}}
            @if($hasAddonRight)
                <span class="inline-flex items-center justify-center px-3 h-10 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm font-medium border-l border-gray-200 dark:border-gray-700">
                    @if($addonRightIcon)
                        <iconify-icon icon="{{ $addonRightIcon }}" class="{{ $addonRight ? 'mr-1.5' : '' }}"></iconify-icon>
                    @endif
                    {{ $addonRight }}
                </span>
            @endif
        </div>
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($step !== null) step="{{ $step }}" @endif
            {{ $attributes->class(['form-control', 'form-input']) }}
        >
    @endif

    @if($hint)
        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $hint }}</div>
    @endif
</div>

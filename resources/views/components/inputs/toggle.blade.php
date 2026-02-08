@props([
    'label' => null,
    'name' => null,
    'checked' => false,
    'value' => 1,
    'disabled' => false,
    'hint' => null,
    'size' => 'md', // sm, md, lg
    'labelPosition' => 'right', // left, right
])

@php
    $isChecked = $checked || old($name);

    // Size classes
    $sizes = [
        'sm' => [
            'track' => 'w-9 h-5',
            'thumb' => 'after:h-4 after:w-4',
            'translate' => 'peer-checked:after:translate-x-full',
        ],
        'md' => [
            'track' => 'w-11 h-6',
            'thumb' => 'after:h-5 after:w-5',
            'translate' => 'peer-checked:after:translate-x-full',
        ],
        'lg' => [
            'track' => 'w-14 h-7',
            'thumb' => 'after:h-6 after:w-6',
            'translate' => 'peer-checked:after:translate-x-full',
        ],
    ];

    $sizeConfig = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="flex items-center gap-3 {{ $disabled ? 'opacity-50' : '' }}">
    {{-- Label on left --}}
    @if($label && $labelPosition === 'left')
        <div class="flex flex-col">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
            @if($hint)
                <span class="text-xs text-gray-500 dark:text-gray-500">{{ $hint }}</span>
            @endif
        </div>
    @endif

    <label class="inline-flex items-center cursor-pointer {{ $disabled ? 'pointer-events-none' : '' }}">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ $value }}"
            @if($isChecked) checked @endif
            @if($disabled) disabled @endif
            {{ $attributes->class(['sr-only peer']) }}
        >
        <div class="relative {{ $sizeConfig['track'] }} bg-gray-200 dark:bg-gray-700 rounded-full peer {{ $sizeConfig['translate'] }} rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full {{ $sizeConfig['thumb'] }} after:transition-all peer-checked:bg-primary peer-focus:ring-2 peer-focus:ring-primary/20"></div>

        {{-- Label on right (inline with toggle) --}}
        @if($label && $labelPosition === 'right')
            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
        @endif
    </label>

    {{-- Hint below if label is on right --}}
    @if($hint && $labelPosition === 'right')
        <span class="text-xs text-gray-500 dark:text-gray-500">{{ $hint }}</span>
    @endif
</div>

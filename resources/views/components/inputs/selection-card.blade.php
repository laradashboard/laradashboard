@props([
    'name' => '',
    'value' => '',
    'label' => '',
    'description' => '',
    'icon' => null,
    'selected' => false,
    'type' => 'radio',
    'externalLink' => false,
    'disabled' => false,
    'class' => '',
])

@php
    $isSelected = old($name, $selected ? $value : '') === $value;
@endphp

<div
    x-data="{ isSelected: {{ $isSelected ? 'true' : 'false' }} }"
    @if($type === 'radio')
        x-init="
            $watch('isSelected', val => {
                if (val) $refs.input.checked = true;
            });
            // Listen for other radio buttons in same group
            $el.closest('[data-selection-group]')?.addEventListener('selection-change', (e) => {
                if (e.detail.name === '{{ $name }}' && e.detail.value !== '{{ $value }}') {
                    isSelected = false;
                }
            });
        "
    @endif
    @click="
        @if(!$disabled)
            @if($type === 'radio')
                isSelected = true;
                $refs.input.checked = true;
                $dispatch('selection-change', { name: '{{ $name }}', value: '{{ $value }}' });
            @else
                isSelected = !isSelected;
                $refs.input.checked = isSelected;
            @endif
            $dispatch('input', { name: '{{ $name }}', value: '{{ $value }}', selected: isSelected });
        @endif
    "
    :class="isSelected
        ? 'border-primary bg-primary/5 ring-1 ring-primary'
        : 'border-gray-200 dark:border-gray-700 hover:border-primary/50'"
    class="relative p-4 border rounded-xl cursor-pointer transition-all {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }} {{ $class }}"
    {{ $attributes->whereStartsWith('x-') }}
>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        x-ref="input"
        class="sr-only"
        {{ $isSelected ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
    />

    <div class="flex items-start gap-3">
        @if($icon)
            <div :class="isSelected ? 'text-primary' : 'text-gray-400'" class="mt-0.5 transition-colors">
                <iconify-icon icon="{{ $icon }}" class="text-2xl"></iconify-icon>
            </div>
        @endif

        <div class="flex-1">
            <h4 class="font-medium text-gray-900 dark:text-white">{{ __($label) }}</h4>
            @if($description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __($description) }}</p>
            @endif
            {{ $slot ?? '' }}
        </div>
    </div>

    <!-- Selected Check Icon -->
    <div x-show="isSelected" class="absolute top-3 right-3">
        <iconify-icon icon="lucide:check-circle-2" class="text-primary text-xl"></iconify-icon>
    </div>

    <!-- External Link Icon -->
    @if($externalLink)
        <div x-show="!isSelected" class="absolute top-3 right-3">
            <iconify-icon icon="lucide:external-link" class="text-gray-400 group-hover:text-primary text-lg transition-colors"></iconify-icon>
        </div>
    @endif
</div>

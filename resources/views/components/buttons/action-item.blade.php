@props([
    'href' => '#',
    'icon' => null,
    'label' => '',
    'onClick' => null,
    'class' => '',
    'type' => 'link', // link, button, or modal-trigger
    'modalTarget' => '',
    'closeDropdown' => true, // Auto-close parent dropdown
])

@php
    $baseClasses = 'flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 ' . $class;
    $closeAction = $closeDropdown ? 'isOpen = false; openedWithKeyboard = false;' : '';
@endphp

@if($type === 'link')
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $baseClasses]) }}
        @if($closeDropdown)
            x-on:click="isOpen = false; openedWithKeyboard = false"
        @endif
        role="menuitem"
    >
        @if($icon)
            <iconify-icon icon="{{ $icon }}" class="text-base"></iconify-icon>
        @endif
        {{ $label }}
    </a>
@elseif($type === 'button')
    <button
        type="button"
        {{ $attributes->merge(['class' => $baseClasses]) }}
        @if($onClick)
            x-on:click="{{ $closeAction }} {{ $onClick }}"
        @elseif($closeDropdown)
            x-on:click="isOpen = false; openedWithKeyboard = false"
        @endif
        role="menuitem"
    >
        @if($icon)
            <iconify-icon icon="{{ $icon }}" class="text-base"></iconify-icon>
        @endif
        {{ $label }}
    </button>
@elseif($type === 'modal-trigger')
    <button
        type="button"
        {{ $attributes->merge(['class' => $baseClasses]) }}
        @if($modalTarget)
            x-on:click="{{ $modalTarget }} = true; $nextTick(() => { isOpen = false; openedWithKeyboard = false; })"
        @endif
        role="menuitem"
    >
        @if($icon)
            <iconify-icon icon="{{ $icon }}" class="text-base"></iconify-icon>
        @endif
        {{ $label }}
    </button>
@endif

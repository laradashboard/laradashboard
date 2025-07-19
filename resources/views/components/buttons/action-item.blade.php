@props([
    'href' => '#',
    'icon' => null,
    'label' => '',
    'onClick' => null,
    'class' => '',
    'type' => 'link', // link, button, or modal-trigger
    'modalTarget' => '',
])

@if($type === 'link')
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => 'flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 ' . $class]) }}
        role="menuitem"
    >
        @if($icon)
            <iconify-icon icon="{{ $icon }}" class="text-base"></iconify-icon>
        @endif
        {{ $label }}
    </a>
@elseif($type === 'button')
    <button
        {{ $attributes->merge(['class' => 'flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 ' . $class]) }}
        @if($onClick)
            onclick="{{ $onClick }}"
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
        {{ $attributes->merge(['class' => 'flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 ' . $class]) }}
        @if($modalTarget)
            x-on:click="{{ $modalTarget }} = true"
        @endif
        role="menuitem"
    >
        @if($icon)
            <iconify-icon icon="{{ $icon }}" class="text-base"></iconify-icon>
        @endif
        {{ $label }}
    </button>
@endif

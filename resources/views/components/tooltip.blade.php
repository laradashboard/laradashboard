@props([
    'id' => null,
    'title' => '',
    'description' => '',
    'position' => 'top', // top, bottom, left, right
    'width' => '',
    'arrowAlign' => 'center', // left, center, right,
])

@php
$positions = [
    'top' => 'bottom-full mb-2 left-1/2 -translate-x-1/2',
    'bottom' => 'top-full mt-2 left-1/2 -translate-x-1/2',
    'left' => 'right-full mr-2 top-1/2 -translate-y-1/2',
    'right' => 'left-full ml-2 top-1/2 -translate-y-1/2',
];
$positionClass = $positions[$position] ?? $positions['top'];

$arrowPositions = [
    'top' => 'top-full -mt-1.5',
    'bottom' => 'bottom-full -mb-1.5',
    'left' => 'left-full -ml-1 top-1/2 -translate-y-1/2',
    'right' => 'right-full -mr-1 top-1/2 -translate-y-1/2',
];
$arrowPositionClass = $arrowPositions[$position] ?? $arrowPositions['top'];

$arrowAlignClass = [
    'left' => 'left-4',
    'center' => 'left-1/2 -translate-x-1/2',
    'right' => 'right-4',
][$arrowAlign] ?? 'left-1/2 -translate-x-1/2';

// Only apply horizontal alignment for top/bottom positions
$arrowAlignClass = in_array($position, ['top', 'bottom']) ? $arrowAlignClass : '';

$tooltipBg = 'bg-gray-900 text-white dark:bg-gray-700 dark:text-gray-100';
@endphp

<div
    x-data="{
        open: false,
        show() { this.open = true },
        hide() { this.open = false }
    }"
    class="relative {{ !$width ? 'w-fit' : '' }}"
    style="{{ $width ? "width: {$width};" : '' }}"
>
    <!-- Trigger -->
    <div
        @mouseenter="show()"
        @mouseleave="hide()"
        @focus="show()"
        @blur="hide()"
        tabindex="0"
        aria-describedby="{{ $id }}"
        class="outline-none"
    >
        {{ $slot }}
    </div>

    <!-- Tooltip -->
    <div
        id="{{ $id }}"
        x-show="open"
        x-transition.opacity.duration.250ms
        class="absolute z-20 px-3 py-2 text-sm rounded-md shadow-md opacity-0 invisible transition-all duration-250 {{ $tooltipBg }} {{ $positionClass }} text-center"
        :class="{ 'opacity-100 visible': open, 'opacity-0 invisible': !open }"
        role="tooltip"
        style="min-width: 160px;"
    >
        @if($title)
            <span class="block text-sm">{{ $title }}</span>
        @endif

        @if($description)
            <p class="text-xs opacity-90">{{ $description }}</p>
        @endif

        <!-- Arrow -->
        <div
            x-show="open"
            x-transition.opacity.duration.150ms
            class="absolute w-2.5 h-2.5 rotate-45 {{ $tooltipBg }} {{ $arrowPositionClass }} {{ $arrowAlignClass }}"
        >
        </div>
    </div>
</div>

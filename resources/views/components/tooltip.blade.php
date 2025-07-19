@props([
    'id' => null,
    'title' => '',
    'description' => '',
    'position' => 'top', // top, bottom, left, right
    'width' => ''
])

@php
$positions = [
    'top' => 'bottom-full mb-2 left-1/2 -translate-x-1/2',
    'bottom' => 'top-full mt-2 left-1/2 -translate-x-1/2',
    'left' => 'right-full mr-2 top-1/2 -translate-y-1/2',
    'right' => 'left-full ml-2 top-1/2 -translate-y-1/2',
];
$positionClass = $positions[$position] ?? $positions['top'];
@endphp

<div class="relative {{ !$width ? 'w-fit' : '' }}" style="{{ $width ? "width: {$width};" : '' }}">
    <div data-tooltip-target="{{ $id }}">
        {{ $slot }}
    </div>

    <div
        id="{{ $id }}"
        class="pointer-events-none {{ $positionClass }} absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700"
        role="tooltip"
    >
        @if($title)
            <span class="text-sm font-medium text-white">{{ $title }}</span>
        @endif

        @if($description)
            <p class="text-balance text-white/90">{{ $description }}</p>
        @endif

        <div class="tooltip-arrow" data-popper-arrow></div>
    </div>
</div>

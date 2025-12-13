@props([
    'icon' => null,
    'label' => '',
    'description' => '',
    'href' => null,
    'click' => null,
])

@if($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => 'flex items-start gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors']) }}
    >
        @if($icon)
            <iconify-icon icon="{{ $icon }}" width="18" height="18" class="text-gray-400 dark:text-gray-500 mt-0.5 shrink-0"></iconify-icon>
        @endif
        <div class="flex-1 min-w-0">
            <div class="font-medium">{{ $label }}</div>
            @if($description)
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</div>
            @endif
        </div>
    </a>
@else
    <button
        type="button"
        @if($click) @click="{{ $click }}" @endif
        {{ $attributes->merge(['class' => 'w-full flex items-start gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left']) }}
    >
        @if($icon)
            <iconify-icon icon="{{ $icon }}" width="18" height="18" class="text-gray-400 dark:text-gray-500 mt-0.5 shrink-0"></iconify-icon>
        @endif
        <div class="flex-1 min-w-0">
            <div class="font-medium">{{ $label }}</div>
            @if($description)
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</div>
            @endif
        </div>
    </button>
@endif

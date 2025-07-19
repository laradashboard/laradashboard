@props([
    'text' => __('Cancel'),
    'icon' => null,
    'event' => 'close-drawer',
    'class' => 'btn-default dark:text-gray-300 dark:hover:bg-gray-700',
])

<button
    @click.prevent="$dispatch('{{ $event }}')"
    {{ $attributes->merge(['class' => $class]) }}
>
    @if($icon)
        <iconify-icon icon="{{ $icon }}" class="mr-1"></iconify-icon>
    @endif
    {{ $text }}
</button>

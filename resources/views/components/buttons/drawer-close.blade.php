@props([
    'text' => __('Cancel'),
    'icon' => 'bi bi-x-circle',
    'event' => 'close-drawer',
    'class' => 'btn-default dark:text-gray-300 dark:hover:bg-gray-700',
])

<button 
    @click.prevent="$dispatch('{{ $event }}')" 
    {{ $attributes->merge(['class' => $class]) }}
>
    @if($icon)
        <i class="{{ $icon }} mr-1"></i>
    @endif
    {{ $text }}
</button>

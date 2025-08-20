@props([
    'column',
    'item',
])

@php
    $value = data_get($item, $column['name']);
    $type = $column['type'] ?? 'text';
    $format = $column['format'] ?? null;
@endphp

@switch($type)
    @case('image')
        @if($value)
            <img src="{{ $value }}" 
                 alt="{{ $column['alt'] ?? '' }}" 
                 class="{{ $column['class'] ?? 'w-10 h-10 rounded-full' }}">
        @else
            <span class="text-gray-400">-</span>
        @endif
        @break
        
    @case('badge')
        @if($value)
            <span class="badge {{ $column['badgeClass'] ?? '' }}">
                {{ $format ? $format($value) : $value }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
        @break
        
    @case('boolean')
        @if($value)
            <iconify-icon icon="lucide:check" class="text-green-600"></iconify-icon>
        @else
            <iconify-icon icon="lucide:x" class="text-red-600"></iconify-icon>
        @endif
        @break
        
    @case('date')
        @if($value)
            {{ $value instanceof \Carbon\Carbon ? $value->format($format ?? 'Y-m-d') : $value }}
        @else
            <span class="text-gray-400">-</span>
        @endif
        @break
        
    @case('datetime')
        @if($value)
            {{ $value instanceof \Carbon\Carbon ? $value->format($format ?? 'Y-m-d H:i:s') : $value }}
        @else
            <span class="text-gray-400">-</span>
        @endif
        @break
        
    @case('link')
        @if($value)
            <a href="{{ $column['href'] ? $column['href']($item) : $value }}" 
               class="text-primary hover:underline"
               @if($column['target'] ?? false) target="{{ $column['target'] }}" @endif>
                {{ $format ? $format($value) : $value }}
            </a>
        @else
            <span class="text-gray-400">-</span>
        @endif
        @break
        
    @case('html')
        {!! $value !!}
        @break
        
    @case('custom')
        @if(isset($column['component']))
            <x-dynamic-component :component="$column['component']" :item="$item" :value="$value" />
        @elseif(isset($column['render']))
            {!! $column['render']($item, $value) !!}
        @else
            {{ $value }}
        @endif
        @break
        
    @default
        @if($value !== null && $value !== '')
            {{ $format ? $format($value) : $value }}
        @else
            <span class="text-gray-400">-</span>
        @endif
@endswitch
@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'columns' => 2,
    'class' => '',
])

@php
    $colClasses = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    ];
    $gridClass = $colClasses[$columns] ?? $colClasses[2];
@endphp

<div class="space-y-4 {{ $class }}" data-selection-group="{{ $name }}" {{ $attributes }}>
    @if($label)
        <label class="form-label">
            {{ __($label) }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="grid {{ $gridClass }} gap-4">
        {{ $slot }}
    </div>
</div>

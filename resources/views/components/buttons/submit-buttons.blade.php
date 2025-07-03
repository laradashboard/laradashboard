@props([
    'submitLabel' => __('Save'),
    'showSubmit' => true,
    'cancelLabel' => __('Cancel'),
    'cancelUrl' => null,
    'id' => null,
    'showIcon' => true,
    'class' => null,
    'classNames' => [
        'wrapper' => 'flex justify-start gap-4',
        'primary' => 'btn-primary',
        'cancel' => 'btn-default',
    ],
])

{{-- fix this line --}}
<div class="{{ $classNames['wrapper'] ?? 'flex justify-start gap-4 ' }}{{ $class }}">
    @if ($showSubmit)
        @if (empty($id))
            <button type="submit" class="{{ $classNames['primary'] ?? 'btn-primary' }}">
                @if ($showIcon)
                    <i class="bi bi-check-circle mr-2"></i>
                @endif

                @if (!empty($submitLabel))
                    {{ $submitLabel }}
                @endif

                {{-- Fallback for when submitLabel and icon both are empty --}}
                {{-- This ensures that the button is not empty --}}
                @if (empty($submitLabel) && $showIcon)
                    {{ __('Save') }}
                @endif
            </button>
        @else
            <button type="button" id="{{ $id }}" class="{{ $classNames['primary'] ?? 'btn-primary' }}">
                @if ($showIcon)
                    <i class="bi bi-check-circle mr-2"></i>
                @endif

                @if (!empty($submitLabel))
                    {{ $submitLabel }}
                @endif

                {{-- Fallback for when submitLabel and icon both are empty --}}
                {{-- This ensures that the button is not empty --}}
                @if (empty($submitLabel) && $showIcon)
                    {{ __('Save') }}
                @endif
            </button>
        @endif
    @endif

    @if (!empty($cancelLabel) && !empty($cancelUrl))
        <a href="{{ $cancelUrl }}" class="{{ $classNames['cancel'] ?? 'btn-default' }}">{{ $cancelLabel }}</a>
    @endif
</div>

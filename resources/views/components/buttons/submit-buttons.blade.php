@props([
    'submitLabel' => __('Save'),
    'showSubmit' => true,
    'cancelLabel' => __('Cancel'),
    'cancelUrl' => null,
    'id' => null,
    'showIcon' => false,
    'classNames' => [
        'wrapper' => 'flex justify-start gap-3',
        'primary' => 'btn-primary',
        'cancel' => 'btn-default',
    ],
])

<div class="{{ $classNames['wrapper'] ?? 'mt-6 flex justify-start gap-4' }}">
    @if ($showSubmit)
        <button type="submit" @if (!empty($id)) id="{{ $id }}" @endif
            class="{{ $classNames['primary'] ?? 'btn-primary' }}">
            @if ($showIcon)
                <iconify-icon icon="lucide:check-circle" class="mr-2"></iconify-icon>
            @endif

            @if (!empty($submitLabel))
                {{ $submitLabel }}
            @endif

            {{-- Fallback for when submitLabel and icon both are empty --}}
            @if (empty($submitLabel) && $showIcon)
                {{ __('Save') }}
            @endif
        </button>
    @endif

    @if (!empty($cancelLabel) && !empty($cancelUrl))
        <a href="{{ $cancelUrl }}" class="{{ $classNames['cancel'] ?? 'btn-default' }}">
            @if ($showIcon)
                <iconify-icon icon="lucide:x-circle" class="mr-2"></iconify-icon>
            @endif

            {{ $cancelLabel }}
        </a>
    @endif
</div>

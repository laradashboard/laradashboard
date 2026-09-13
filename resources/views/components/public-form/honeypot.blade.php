@php
    $guard = app(\App\Services\PublicFormGuardService::class);
@endphp

@if($guard->isHoneypotEnabled())
    <div class="absolute left-[-9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="{{ $guard->honeypotField() }}">{{ __('Company website') }}</label>
        <input
            type="text"
            id="{{ $guard->honeypotField() }}"
            wire:model="{{ $guard->honeypotField() }}"
            tabindex="-1"
            autocomplete="off"
        />
    </div>
@endif

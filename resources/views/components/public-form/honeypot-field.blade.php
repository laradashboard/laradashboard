@php
    $field = app(\App\Services\PublicFormGuardService::class)->honeypotField();
    $enabled = app(\App\Services\PublicFormGuardService::class)->isHoneypotEnabled();
@endphp

@if($enabled)
    <div class="absolute left-[-9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="{{ $field }}">{{ __('Company website') }}</label>
        <input
            type="text"
            id="{{ $field }}"
            name="{{ $field }}"
            tabindex="-1"
            autocomplete="off"
            value=""
        />
    </div>
@endif

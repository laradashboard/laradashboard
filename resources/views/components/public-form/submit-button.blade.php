@props([
    'page',
    'method' => 'submit',
    'loadingTarget' => null,
])

@php
    $recaptchaService = app(\App\Services\RecaptchaService::class);
    $recaptchaEnabled = $recaptchaService->isEnabledForPage($page) && $recaptchaService->getSiteKey() !== '';
    $siteKey = $recaptchaService->getSiteKey();
    $loadingTarget = $loadingTarget ?? $method;
@endphp

<button
    type="button"
    {{ $attributes->merge(['class' => 'btn-primary']) }}
    wire:loading.attr="disabled"
    wire:target="{{ $loadingTarget }}"
    @if($recaptchaEnabled)
        x-data
        x-on:click="window.ldExecuteLivewireRecaptcha($wire, @js($method), @js($siteKey), @js($page), true)"
    @else
        wire:click="{{ $method }}"
    @endif
>
    {{ $slot }}
</button>

@props([
    'page',
    'submitMethod' => 'submit',
    'buttonClass' => 'btn-primary',
    'loadingTarget' => null,
])

@php
    $recaptchaService = app(\App\Services\RecaptchaService::class);
    $isEnabled = $recaptchaService->isEnabledForPage($page);
    $siteKey = $recaptchaService->getSiteKey();
    $loadingTarget = $loadingTarget ?? $submitMethod;
    $badgePosition = config('settings.recaptcha_badge_position', 'left');
@endphp

@if($isEnabled && $siteKey)
    @push('scripts')
        {!! $recaptchaService->getScriptTag() !!}
    @endpush
    @push('styles')
        <style>
            .grecaptcha-badge {
                @if($badgePosition === 'left')
                    left: 20px !important;
                    right: auto !important;
                @else
                    right: 20px !important;
                    left: auto !important;
                @endif
                bottom: 20px !important;
                z-index: 100;
            }
        </style>
    @endpush
@endif

<script>
    window.ldExecuteLivewireRecaptcha = window.ldExecuteLivewireRecaptcha || function (wire, method, siteKey, page, enabled) {
        if (! enabled || ! siteKey) {
            wire[method]();
            return;
        }

        if (typeof grecaptcha === 'undefined') {
            console.error('reCAPTCHA script did not load. Check that the page layout renders the scripts stack.');
            return;
        }

        grecaptcha.ready(function () {
            grecaptcha.execute(siteKey, { action: page }).then(function (token) {
                wire.set('recaptchaToken', token);
                wire[method]();
            });
        });
    };
</script>

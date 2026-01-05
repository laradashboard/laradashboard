{!! Hook::applyFilters(SettingFilterHook::SETTINGS_RECAPTCHA_INTEGRATIONS_TAB_BEFORE_SECTION_START, '') !!}
<x-card>
    <x-slot name="header">
        {{ __('Google reCAPTCHA v3 Settings') }}
    </x-slot>

    <div class="space-y-6">
        <div class="relative">
            <label class="form-label" for="recaptcha_site_key">
                {{ __('reCAPTCHA Site Key') }}
            </label>
            <div class="relative">
                <input
                    type="text"
                    name="recaptcha_site_key"
                    id="recaptcha_site_key"
                    placeholder="{{ __('Enter your reCAPTCHA site key') }}"
                    @if (config('app.demo_mode', false)) disabled @endif
                    class="form-control pr-14"
                    value="{{ config('settings.recaptcha_site_key') ?? '' }}"
                />
                <button type="button"
                    onclick="copyRecaptchaToClipboard('recaptcha_site_key')"
                    class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-300 flex items-center justify-center w-6 h-6 hover:text-gray-700 dark:hover:text-gray-100 transition-colors">
                    <iconify-icon icon="lucide:copy" width="18" height="18"></iconify-icon>
                </button>
            </div>

            @if (config('app.demo_mode', false))
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Editing this field is disabled in demo mode.') }}
            </div>
            @endif
        </div>

        <div class="relative">
            <label class="form-label" for="recaptcha_secret_key">
                {{ __('reCAPTCHA Secret Key') }}
            </label>
            <x-inputs.password
                name="recaptcha_secret_key"
                id="recaptcha_secret_key"
                :value="config('settings.recaptcha_secret_key') ?? ''"
                placeholder="{{ __('Enter your reCAPTCHA secret key') }}"
                :required="false"
                :disabled="config('app.demo_mode', false)"
                :showTooltip="__('Show reCAPTCHA secret')"
            />
            @if (config('app.demo_mode', false))
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Editing this field is disabled in demo mode.') }}
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="relative">
                <label class="form-label" for="recaptcha_score_threshold">
                    {{ __('reCAPTCHA Score Threshold') }}
                </label>
                <input
                    type="number"
                    name="recaptcha_score_threshold"
                    id="recaptcha_score_threshold"
                    placeholder="{{ __('0.5') }}"
                    @if (config('app.demo_mode', false)) disabled @endif
                    class="form-control"
                    value="{{ config('settings.recaptcha_score_threshold', '0.5') }}"
                    min="0" max="1" step="0.1"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Set the minimum score (0.0-1.0) required to pass reCAPTCHA v3 verification. Default: 0.5') }}
                </p>

                @if (config('app.demo_mode', false))
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Editing this field is disabled in demo mode.') }}
                </div>
                @endif
            </div>
            <div class="relative">
                <label class="form-label" for="recaptcha_badge_position">
                    {{ __('reCAPTCHA Badge Position') }}
                </label>
                @php
                    $badgePosition = config('settings.recaptcha_badge_position', 'left');
                @endphp
                <select
                    name="recaptcha_badge_position"
                    id="recaptcha_badge_position"
                    class="form-control"
                    @if (config('app.demo_mode', false)) disabled @endif
                >
                    <option value="left" @if($badgePosition === 'left') selected @endif>{{ __('Left Corner') }}</option>
                    <option value="right" @if($badgePosition === 'right') selected @endif>{{ __('Right Corner') }}</option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Choose where the reCAPTCHA badge appears on the screen.') }}
                </p>
                @if (config('app.demo_mode', false))
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Editing this field is disabled in demo mode.') }}
                </div>
                @endif
            </div>
        </div>

        <div class="relative">
            <label class="form-label">
                {{ __('Enable reCAPTCHA on Pages') }}
            </label>
            @php
                $availablePages = \App\Services\RecaptchaService::getAvailablePages();
                $enabledPages = json_decode(config('settings.recaptcha_enabled_pages', '[]'), true) ?: [];
            @endphp

            <div class="space-y-2">
                @foreach($availablePages as $page => $label)
                <label class="flex items-center">
                    <input type="checkbox" name="recaptcha_enabled_pages[]" value="{{ $page }}"
                        @if(in_array($page, $enabledPages)) checked @endif
                        @if (config('app.demo_mode', false)) disabled @endif
                        class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                </label>
                @endforeach
            </div>

            @if (config('app.demo_mode', false))
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Editing these options are disabled in demo mode.') }}
            </div>
            @endif

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                {{ __('Learn more about Google reCAPTCHA and how to set it up:') }}
                <a href="https://www.google.com/recaptcha/admin" target="_blank" class="text-primary hover:underline">
                    {{ __('Google reCAPTCHA v3') }}
                </a>
            </p>
        </div>
    </div>
</x-card>
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_RECAPTCHA_INTEGRATIONS_TAB_BEFORE_SECTION_END, '') !!}

@push('scripts')
<script>
function copyRecaptchaToClipboard(inputId) {
    const input = document.getElementById(inputId);
    if (!input || !input.value.trim()) {
        if (typeof window.showToast === 'function') {
            window.showToast('warning', 'Warning', 'No key to copy');
        }
        return;
    }
    const textarea = document.createElement('textarea');
    textarea.value = input.value;
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        if (typeof window.showToast === 'function') {
            window.showToast('success', 'Copied!', 'Key copied to clipboard');
        }
    } catch (err) {
        if (typeof window.showToast === 'function') {
            window.showToast('error', 'Error', 'Failed to copy to clipboard');
        }
    } finally {
        document.body.removeChild(textarea);
    }
}
</script>
@endpush

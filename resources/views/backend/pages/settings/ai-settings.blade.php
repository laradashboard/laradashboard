{!! Hook::applyFilters(SettingFilterHook::SETTINGS_AI_INTEGRATIONS_TAB_BEFORE_SECTION_START, '') !!}
<x-card>
    <x-slot name="header">
        {{ __('AI Integration') }}
    </x-slot>

    <div class="space-y-4">
        <!-- Default AI Provider and Max Tokens -->
        <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
            <div class="flex-1">
                <label for="ai_default_provider" class="form-label">
                    {{ __('Default AI Provider') }}
                </label>
                <select name="ai_default_provider"
                    id="ai_default_provider"
                    class="form-control">
                    <option value="openai" {{ (config('settings.ai_default_provider', 'openai') == 'openai') ? 'selected' : '' }}>
                        {{ __('OpenAI') }}
                    </option>
                    <option value="claude" {{ (config('settings.ai_default_provider', 'openai') == 'claude') ? 'selected' : '' }}>
                        {{ __('Claude (Anthropic)') }}
                    </option>
                </select>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                    {{ __('Select the default AI provider for your application.') }}
                </p>
            </div>
            <div class="flex-1">
                <label for="ai_max_tokens" class="form-label">
                    {{ __('Max Tokens') }}
                </label>
                <input type="number" name="ai_max_tokens"
                    id="ai_max_tokens"
                    value="{{ config('settings.ai_max_tokens', 2000) }}"
                    placeholder="4096"
                    min="100"
                    class="form-control">
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                    {{ __('Maximum tokens for AI responses (100-8000). OpenAI GPT-3.5: max 4096 total, Claude: up to 100k.') }}
                </p>
            </div>
        </div>

        <!-- OpenAI API Key -->
        <div class="relative">
            <label for="ai_openai_api_key" class="form-label">
                {{ __('OpenAI API Key') }}
            </label>
            <div class="relative">
                <input type="text" name="ai_openai_api_key"
                    id="ai_openai_api_key"
                    value="{{ config('settings.ai_openai_api_key') ?? '' }}"
                    placeholder="{{ __('Enter your OpenAI API key') }}"
                    class="form-control pr-14">
                <button type="button"
                    onclick="copyToClipboard('ai_openai_api_key')"
                    class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-300 flex items-center justify-center w-6 h-6 hover:text-gray-700 dark:hover:text-gray-100 transition-colors">
                    <iconify-icon icon="lucide:copy" width="18" height="18"></iconify-icon>
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                {{ __('Get your API key from:') }}
                <a href="https://platform.openai.com/api-keys" target="_blank" class="text-primary hover:underline">
                    {{ __('OpenAI Platform') }}
                </a>
            </p>
        </div>

        <!-- Claude API Key -->
        <div class="relative">
            <label for="ai_claude_api_key" class="form-label">
                {{ __('Claude API Key (Anthropic)') }}
            </label>
            <div class="relative">
                <input type="text" name="ai_claude_api_key"
                    id="ai_claude_api_key"
                    value="{{ config('settings.ai_claude_api_key') ?? '' }}"
                    placeholder="{{ __('Enter your Claude API key') }}"
                    class="form-control pr-14">
                <button type="button"
                    onclick="copyToClipboard('ai_claude_api_key')"
                    class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-300 flex items-center justify-center w-6 h-6 hover:text-gray-700 dark:hover:text-gray-100 transition-colors">
                    <iconify-icon icon="lucide:copy" width="18" height="18"></iconify-icon>
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                {{ __('Get your API key from:') }}
                <a href="https://console.anthropic.com/" target="_blank" class="text-primary hover:underline">
                    {{ __('Anthropic Console') }}
                </a>
            </p>
        </div>
    </div>
    {!! Hook::applyFilters(SettingFilterHook::SETTINGS_AI_INTEGRATIONS_TAB_BEFORE_SECTION_END, '') !!}
</x-card>
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_AI_INTEGRATIONS_TAB_AFTER_SECTION_END, '') !!}

<script>
function copyToClipboard(inputId) {
    const input = document.getElementById(inputId);
    if (!input || !input.value.trim()) {
        if (typeof window.showToast === 'function') {
            window.showToast('warning', 'Warning', 'No API key to copy');
        }
        return;
    }
    
    // Create a temporary textarea element to copy the text
    const textarea = document.createElement('textarea');
    textarea.value = input.value;
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        if (typeof window.showToast === 'function') {
            window.showToast('success', 'Copied!', 'API key copied to clipboard');
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

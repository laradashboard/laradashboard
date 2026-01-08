@php
    $openaiEnvKey = config('ai.openai.api_key');
    $openaiMaskedEnv = $openaiEnvKey ? substr($openaiEnvKey, 0, 7) . '...' . substr($openaiEnvKey, -4) : null;
    $claudeEnvKey = config('ai.anthropic.api_key');
    $claudeMaskedEnv = $claudeEnvKey ? substr($claudeEnvKey, 0, 7) . '...' . substr($claudeEnvKey, -4) : null;
    $geminiEnvKey = config('ai.gemini.api_key');
    $geminiMaskedEnv = $geminiEnvKey ? substr($geminiEnvKey, 0, 7) . '...' . substr($geminiEnvKey, -4) : null;
    $ollamaEnvUrl = config('ai.ollama.base_url');

    $openaiConfigured = config('settings.ai_openai_api_key') ?: $openaiEnvKey;
    $claudeConfigured = config('settings.ai_claude_api_key') ?: $claudeEnvKey;
    $geminiConfigured = config('settings.ai_gemini_api_key') ?: $geminiEnvKey;
    $ollamaConfigured = config('settings.ai_ollama_base_url') ?: $ollamaEnvUrl;

    $defaultProvider = config('settings.ai_default_provider', 'openai');
@endphp

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_AI_INTEGRATIONS_TAB_BEFORE_SECTION_START, '') !!}
<x-card>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:sparkles" width="20" height="20" class="text-primary"></iconify-icon>
            {{ __('AI Integration') }}
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Global Settings -->
        <div class="p-4 border border-gray-200 rounded-xl dark:border-gray-700 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-800/50 dark:to-gray-900/30">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700">
                    <iconify-icon icon="lucide:settings-2" width="18" height="18" class="text-gray-600 dark:text-gray-300"></iconify-icon>
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('Global Settings') }}</h4>
            </div>
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
                        <option value="gemini" {{ (config('settings.ai_default_provider', 'openai') == 'gemini') ? 'selected' : '' }}>
                            {{ __('Gemini (Google)') }}
                        </option>
                        <option value="ollama" {{ (config('settings.ai_default_provider', 'openai') == 'ollama') ? 'selected' : '' }}>
                            {{ __('Ollama (Local)') }}
                        </option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Select the default AI provider for your application.') }}
                    </p>
                </div>
                <div class="flex-1">
                    <label for="ai_max_tokens" class="form-label">
                        {{ __('Max Tokens') }}
                    </label>
                    <div class="relative">
                        <input type="number" name="ai_max_tokens"
                            id="ai_max_tokens"
                            value="{{ config('settings.ai_max_tokens', 4096) }}"
                            placeholder="4096"
                            min="100"
                            max="16384"
                            class="form-control pr-24">
                        <span id="ai_max_tokens_limit" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 dark:text-gray-500 font-mono">
                            / 16384
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="ai_max_tokens_hint">
                        {{ __('Maximum output tokens for AI responses.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- AI Providers Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <!-- OpenAI Card -->
            <div class="relative p-4 border border-gray-200 rounded-xl dark:border-gray-700 bg-gradient-to-br from-emerald-50/50 to-teal-50/30 dark:from-emerald-950/20 dark:to-teal-950/10 overflow-hidden group hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-emerald-500/10 to-transparent rounded-bl-full"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/20">
                                <iconify-icon icon="simple-icons:openai" width="20" height="20" class="text-white"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('OpenAI') }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">GPT-4o, GPT-4, DALL-E</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5" data-provider-badges="openai">
                            @if($defaultProvider === 'openai')
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300 provider-active-badge">
                                    <iconify-icon icon="lucide:zap" width="10" height="10"></iconify-icon>
                                    {{ __('Active') }}
                                </span>
                            @endif
                            @if($openaiConfigured)
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full dark:bg-emerald-900/50 dark:text-emerald-300 provider-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                    {{ __('Configured') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-gray-400 provider-not-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label for="ai_openai_api_key" class="form-label text-sm">
                                {{ __('API Key') }}
                            </label>
                            <div class="relative">
                                <input type="password" name="ai_openai_api_key"
                                    id="ai_openai_api_key"
                                    value="{{ config('settings.ai_openai_api_key') ?? '' }}"
                                    placeholder="{{ $openaiMaskedEnv ? $openaiMaskedEnv . ' (' . __('from .env') . ')' : 'sk-...' }}"
                                    data-env-fallback="{{ $openaiEnvKey ?? '' }}"
                                    class="form-control pr-20 text-sm font-mono">
                                <div class="absolute flex items-center gap-1 -translate-y-1/2 right-2 top-1/2">
                                    <button type="button"
                                        onclick="togglePasswordVisibility('ai_openai_api_key', this)"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded">
                                        <iconify-icon icon="lucide:eye" width="16" height="16"></iconify-icon>
                                    </button>
                                    <button type="button"
                                        onclick="copyAiToClipboard('ai_openai_api_key')"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded">
                                        <iconify-icon icon="lucide:copy" width="16" height="16"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="ai_openai_model" class="form-label text-sm">
                                {{ __('Model') }}
                            </label>
                            <select name="ai_openai_model" id="ai_openai_model" class="form-control text-sm">
                                @php $currentOpenaiModel = config('settings.ai_openai_model') ?: config('ai.openai.model', 'gpt-4o-mini'); @endphp
                                <option value="gpt-4o-mini" {{ $currentOpenaiModel == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini ({{ __('Fast & Affordable') }})</option>
                                <option value="gpt-4o" {{ $currentOpenaiModel == 'gpt-4o' ? 'selected' : '' }}>GPT-4o ({{ __('Most Capable') }})</option>
                                <option value="gpt-4-turbo" {{ $currentOpenaiModel == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                                <option value="gpt-3.5-turbo" {{ $currentOpenaiModel == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo ({{ __('Legacy') }})</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/50 dark:border-gray-700/50">
                        <a href="https://platform.openai.com/api-keys" target="_blank" class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors">
                            <iconify-icon icon="lucide:external-link" width="12" height="12"></iconify-icon>
                            {{ __('Get API Key') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Claude Card -->
            <div class="relative p-4 border border-gray-200 rounded-xl dark:border-gray-700 bg-gradient-to-br from-orange-50/50 to-amber-50/30 dark:from-orange-950/20 dark:to-amber-950/10 overflow-hidden group hover:border-orange-300 dark:hover:border-orange-700 transition-colors">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-orange-500/10 to-transparent rounded-bl-full"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 shadow-lg shadow-orange-500/20">
                                <iconify-icon icon="simple-icons:anthropic" width="20" height="20" class="text-white"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('Claude') }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Anthropic AI</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5" data-provider-badges="claude">
                            @if($defaultProvider === 'claude')
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300 provider-active-badge">
                                    <iconify-icon icon="lucide:zap" width="10" height="10"></iconify-icon>
                                    {{ __('Active') }}
                                </span>
                            @endif
                            @if($claudeConfigured)
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full dark:bg-emerald-900/50 dark:text-emerald-300 provider-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                    {{ __('Configured') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-gray-400 provider-not-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label for="ai_claude_api_key" class="form-label text-sm">
                                {{ __('API Key') }}
                            </label>
                            <div class="relative">
                                <input type="password" name="ai_claude_api_key"
                                    id="ai_claude_api_key"
                                    value="{{ config('settings.ai_claude_api_key') ?? '' }}"
                                    placeholder="{{ $claudeMaskedEnv ? $claudeMaskedEnv . ' (' . __('from .env') . ')' : 'sk-ant-...' }}"
                                    data-env-fallback="{{ $claudeEnvKey ?? '' }}"
                                    class="form-control pr-20 text-sm font-mono">
                                <div class="absolute flex items-center gap-1 -translate-y-1/2 right-2 top-1/2">
                                    <button type="button"
                                        onclick="togglePasswordVisibility('ai_claude_api_key', this)"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded">
                                        <iconify-icon icon="lucide:eye" width="16" height="16"></iconify-icon>
                                    </button>
                                    <button type="button"
                                        onclick="copyAiToClipboard('ai_claude_api_key')"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded">
                                        <iconify-icon icon="lucide:copy" width="16" height="16"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="ai_claude_model" class="form-label text-sm">
                                {{ __('Model') }}
                            </label>
                            <select name="ai_claude_model" id="ai_claude_model" class="form-control text-sm">
                                @php $currentClaudeModel = config('settings.ai_claude_model') ?: config('ai.anthropic.model', 'claude-3-haiku-20240307'); @endphp
                                <option value="claude-3-haiku-20240307" {{ $currentClaudeModel == 'claude-3-haiku-20240307' ? 'selected' : '' }}>Claude 3 Haiku ({{ __('Fast') }})</option>
                                <option value="claude-3-5-sonnet-20241022" {{ $currentClaudeModel == 'claude-3-5-sonnet-20241022' ? 'selected' : '' }}>Claude 3.5 Sonnet ({{ __('Balanced') }})</option>
                                <option value="claude-3-opus-20240229" {{ $currentClaudeModel == 'claude-3-opus-20240229' ? 'selected' : '' }}>Claude 3 Opus ({{ __('Most Capable') }})</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/50 dark:border-gray-700/50">
                        <a href="https://console.anthropic.com/" target="_blank" class="inline-flex items-center gap-1 text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 transition-colors">
                            <iconify-icon icon="lucide:external-link" width="12" height="12"></iconify-icon>
                            {{ __('Get API Key') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gemini Card -->
            <div class="relative p-4 border border-gray-200 rounded-xl dark:border-gray-700 bg-gradient-to-br from-blue-50/50 to-indigo-50/30 dark:from-blue-950/20 dark:to-indigo-950/10 overflow-hidden group hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-500/10 to-transparent rounded-bl-full"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/20">
                                <iconify-icon icon="simple-icons:googlegemini" width="20" height="20" class="text-white"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('Gemini') }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Google AI</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5" data-provider-badges="gemini">
                            @if($defaultProvider === 'gemini')
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300 provider-active-badge">
                                    <iconify-icon icon="lucide:zap" width="10" height="10"></iconify-icon>
                                    {{ __('Active') }}
                                </span>
                            @endif
                            @if($geminiConfigured)
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full dark:bg-emerald-900/50 dark:text-emerald-300 provider-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                    {{ __('Configured') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-gray-400 provider-not-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label for="ai_gemini_api_key" class="form-label text-sm">
                                {{ __('API Key') }}
                            </label>
                            <div class="relative">
                                <input type="password" name="ai_gemini_api_key"
                                    id="ai_gemini_api_key"
                                    value="{{ config('settings.ai_gemini_api_key') ?? '' }}"
                                    placeholder="{{ $geminiMaskedEnv ? $geminiMaskedEnv . ' (' . __('from .env') . ')' : 'AIza...' }}"
                                    data-env-fallback="{{ $geminiEnvKey ?? '' }}"
                                    class="form-control pr-20 text-sm font-mono">
                                <div class="absolute flex items-center gap-1 -translate-y-1/2 right-2 top-1/2">
                                    <button type="button"
                                        onclick="togglePasswordVisibility('ai_gemini_api_key', this)"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded">
                                        <iconify-icon icon="lucide:eye" width="16" height="16"></iconify-icon>
                                    </button>
                                    <button type="button"
                                        onclick="copyAiToClipboard('ai_gemini_api_key')"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded">
                                        <iconify-icon icon="lucide:copy" width="16" height="16"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="ai_gemini_model" class="form-label text-sm">
                                {{ __('Model') }}
                            </label>
                            <select name="ai_gemini_model" id="ai_gemini_model" class="form-control text-sm">
                                @php $currentGeminiModel = config('settings.ai_gemini_model') ?: config('ai.gemini.model', 'gemini-2.0-flash'); @endphp
                                <option value="gemini-2.0-flash" {{ $currentGeminiModel == 'gemini-2.0-flash' ? 'selected' : '' }}>Gemini 2.0 Flash ({{ __('Recommended') }})</option>
                                <option value="gemini-1.5-pro-latest" {{ $currentGeminiModel == 'gemini-1.5-pro-latest' ? 'selected' : '' }}>Gemini 1.5 Pro ({{ __('Most Capable') }})</option>
                                <option value="gemini-1.5-flash-latest" {{ $currentGeminiModel == 'gemini-1.5-flash-latest' ? 'selected' : '' }}>Gemini 1.5 Flash ({{ __('Fast') }})</option>
                                <option value="gemini-1.5-flash-8b-latest" {{ $currentGeminiModel == 'gemini-1.5-flash-8b-latest' ? 'selected' : '' }}>Gemini 1.5 Flash-8B ({{ __('Lightweight') }})</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/50 dark:border-gray-700/50">
                        <a href="https://aistudio.google.com/apikey" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                            <iconify-icon icon="lucide:external-link" width="12" height="12"></iconify-icon>
                            {{ __('Get API Key') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ollama Card -->
            <div class="relative p-4 border border-gray-200 rounded-xl dark:border-gray-700 bg-gradient-to-br from-purple-50/50 to-pink-50/30 dark:from-purple-950/20 dark:to-pink-950/10 overflow-hidden group hover:border-purple-300 dark:hover:border-purple-700 transition-colors">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-purple-500/10 to-transparent rounded-bl-full"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg shadow-purple-500/20">
                                <iconify-icon icon="simple-icons:ollama" width="20" height="20" class="text-white"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('Ollama') }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Local AI - Free & Private') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5" data-provider-badges="ollama">
                            @if($defaultProvider === 'ollama')
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300 provider-active-badge">
                                    <iconify-icon icon="lucide:zap" width="10" height="10"></iconify-icon>
                                    {{ __('Active') }}
                                </span>
                            @endif
                            @if($ollamaConfigured)
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full dark:bg-emerald-900/50 dark:text-emerald-300 provider-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                    {{ __('Configured') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-gray-400 provider-not-configured-badge">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label for="ai_ollama_base_url" class="form-label text-sm">
                                {{ __('Base URL') }}
                            </label>
                            <input type="text" name="ai_ollama_base_url"
                                id="ai_ollama_base_url"
                                value="{{ config('settings.ai_ollama_base_url') ?? '' }}"
                                placeholder="{{ $ollamaEnvUrl ?: 'http://localhost:11434' }}"
                                class="form-control text-sm font-mono">
                        </div>
                        <div>
                            <label for="ai_ollama_model" class="form-label text-sm">
                                {{ __('Model') }}
                            </label>
                            <div class="relative">
                                <input type="text" name="ai_ollama_model"
                                    id="ai_ollama_model"
                                    value="{{ config('settings.ai_ollama_model') ?? '' }}"
                                    placeholder="{{ config('ai.ollama.model', 'llama3.2') }}"
                                    list="ollama_models_list"
                                    class="form-control text-sm">
                                <datalist id="ollama_models_list">
                                    <option value="llama3.2">Llama 3.2 ({{ __('Recommended') }})</option>
                                    <option value="llama3.2:1b">Llama 3.2 1B ({{ __('Lightweight') }})</option>
                                    <option value="llama3.1">Llama 3.1</option>
                                    <option value="mistral">Mistral</option>
                                    <option value="mixtral">Mixtral</option>
                                    <option value="codellama">Code Llama</option>
                                    <option value="phi3">Phi-3</option>
                                    <option value="gemma2">Gemma 2</option>
                                    <option value="qwen2.5">Qwen 2.5</option>
                                </datalist>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Run') }} <code class="px-1.5 py-0.5 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded text-xs font-mono">ollama pull llama3.2</code> {{ __('to install') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                        <a href="https://ollama.ai" target="_blank" class="inline-flex items-center gap-1 text-xs text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 transition-colors">
                            <iconify-icon icon="lucide:download" width="12" height="12"></iconify-icon>
                            {{ __('Download Ollama') }}
                        </a>
                        <a href="https://ollama.ai/library" target="_blank" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                            <iconify-icon icon="lucide:library" width="12" height="12"></iconify-icon>
                            {{ __('Model Library') }}
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    {!! Hook::applyFilters(SettingFilterHook::SETTINGS_AI_INTEGRATIONS_TAB_BEFORE_SECTION_END, '') !!}
</x-card>
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_AI_INTEGRATIONS_TAB_AFTER_SECTION_END, '') !!}

<script>
// Token limits per provider (based on max output tokens)
const providerTokenLimits = {
    openai: {
        default: 16384,
        models: {
            'gpt-4o': 16384,
            'gpt-4o-mini': 16384,
            'gpt-4-turbo': 4096,
            'gpt-3.5-turbo': 4096
        },
        hint: 'GPT-4o supports up to 16K output tokens.'
    },
    claude: {
        default: 8192,
        models: {
            'claude-3-5-sonnet-20241022': 8192,
            'claude-3-haiku-20240307': 4096,
            'claude-3-opus-20240229': 4096
        },
        hint: 'Claude 3.5 Sonnet supports up to 8K output tokens.'
    },
    gemini: {
        default: 8192,
        models: {
            'gemini-2.0-flash': 8192,
            'gemini-1.5-pro-latest': 8192,
            'gemini-1.5-flash-latest': 8192,
            'gemini-1.5-flash-8b-latest': 8192
        },
        hint: 'Gemini models support up to 8K output tokens.'
    },
    ollama: {
        default: 4096,
        models: {},
        hint: 'Ollama token limit depends on model and available memory.'
    }
};

function updateTokenLimits() {
    const providerSelect = document.getElementById('ai_default_provider');
    const maxTokensInput = document.getElementById('ai_max_tokens');
    const maxTokensLimit = document.getElementById('ai_max_tokens_limit');
    const maxTokensHint = document.getElementById('ai_max_tokens_hint');

    if (!providerSelect || !maxTokensInput) return;

    const provider = providerSelect.value;
    const config = providerTokenLimits[provider] || providerTokenLimits.openai;

    // Get model-specific limit if available
    let modelSelect = null;
    let maxLimit = config.default;

    if (provider === 'openai') {
        modelSelect = document.getElementById('ai_openai_model');
    } else if (provider === 'claude') {
        modelSelect = document.getElementById('ai_claude_model');
    } else if (provider === 'gemini') {
        modelSelect = document.getElementById('ai_gemini_model');
    }

    if (modelSelect && config.models[modelSelect.value]) {
        maxLimit = config.models[modelSelect.value];
    }

    // Update input max attribute
    maxTokensInput.max = maxLimit;
    maxTokensInput.placeholder = Math.min(4096, maxLimit);

    // Update the limit display
    if (maxTokensLimit) {
        maxTokensLimit.textContent = `/ ${maxLimit.toLocaleString()}`;
    }

    // Update hint text
    if (maxTokensHint) {
        maxTokensHint.textContent = config.hint;
    }

    // Adjust current value if it exceeds new max
    const currentValue = parseInt(maxTokensInput.value);
    if (currentValue > maxLimit) {
        maxTokensInput.value = maxLimit;
    }
}

// Update active badge when provider changes
function updateActiveBadge(selectedProvider) {
    const providers = ['openai', 'claude', 'gemini', 'ollama'];
    const activeBadgeHtml = `
        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300 provider-active-badge">
            <iconify-icon icon="lucide:zap" width="10" height="10"></iconify-icon>
            {{ __('Active') }}
        </span>
    `;

    providers.forEach(function(provider) {
        const badgeContainer = document.querySelector(`[data-provider-badges="${provider}"]`);
        if (!badgeContainer) return;

        // Remove existing active badge
        const existingActive = badgeContainer.querySelector('.provider-active-badge');
        if (existingActive) {
            existingActive.remove();
        }

        // Add active badge to selected provider
        if (provider === selectedProvider) {
            badgeContainer.insertAdjacentHTML('afterbegin', activeBadgeHtml);
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTokenLimits();

    // Listen for provider changes
    const providerSelect = document.getElementById('ai_default_provider');
    if (providerSelect) {
        providerSelect.addEventListener('change', function() {
            updateTokenLimits();
            updateActiveBadge(this.value);
        });
    }

    // Listen for model changes
    ['ai_openai_model', 'ai_claude_model', 'ai_gemini_model'].forEach(function(id) {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', updateTokenLimits);
        }
    });
});

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = button.querySelector('iconify-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('icon', 'lucide:eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('icon', 'lucide:eye');
    }
}

function copyAiToClipboard(inputId) {
    const input = document.getElementById(inputId);
    const inputValue = input?.value?.trim();
    const fallbackValue = input?.dataset?.envFallback || '';
    const valueToCopy = inputValue || fallbackValue;

    if (!valueToCopy) {
        if (typeof window.showToast === 'function') {
            window.showToast('warning', 'Warning', 'No API key to copy');
        }
        return;
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(valueToCopy).then(() => {
            if (typeof window.showToast === 'function') {
                const source = inputValue ? 'API key' : 'API key from .env';
                window.showToast('success', 'Copied!', source + ' copied to clipboard');
            }
        }).catch(() => {
            fallbackCopy(valueToCopy, inputValue);
        });
    } else {
        fallbackCopy(valueToCopy, inputValue);
    }
}

function fallbackCopy(valueToCopy, inputValue) {
    const textarea = document.createElement('textarea');
    textarea.value = valueToCopy;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand('copy');
        if (typeof window.showToast === 'function') {
            const source = inputValue ? 'API key' : 'API key from .env';
            window.showToast('success', 'Copied!', source + ' copied to clipboard');
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

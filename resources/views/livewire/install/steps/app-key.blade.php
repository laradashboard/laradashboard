<div class="space-y-6">
    {{-- Current Key Display --}}
    <div>
        <label class="form-label">{{ __('Application Encryption Key') }}</label>
        <div class="flex items-center gap-3">
            <div class="flex-1 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg font-mono text-sm text-gray-700 dark:text-gray-300 break-all">
                @if ($appKey)
                    {{ $appKey }}
                @else
                    <span class="text-gray-400 dark:text-gray-500 italic">{{ __('No key generated yet') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Status --}}
    @if ($appKeyGenerated)
        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-green-700 dark:text-green-300">{{ __('Application key is configured') }}</p>
                    <p class="text-sm text-green-600 dark:text-green-400">{{ __('Your application encryption key is ready to use.') }}</p>
                </div>
            </div>
        </div>

        {{-- Regenerate Button (optional) --}}
        <div>
            <x-buttons.button
                variant="secondary"
                wire:click="generateAppKey"
                loadingTarget="generateAppKey"
                loadingText="{{ __('Regenerating...') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                {{ __('Regenerate Key') }}
            </x-buttons.button>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('Only regenerate if you want a new key. The current key was auto-generated.') }}</p>
        </div>
    @else
        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-yellow-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-yellow-700 dark:text-yellow-300">{{ __('Application key not set') }}</p>
                    <p class="text-sm text-yellow-600 dark:text-yellow-400">{{ __('Click the button below to generate a secure encryption key.') }}</p>
                </div>
            </div>
        </div>

        {{-- Generate Button --}}
        <div>
            <x-buttons.button
                variant="primary"
                wire:click="generateAppKey"
                loadingTarget="generateAppKey"
                loadingText="{{ __('Generating...') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                {{ __('Generate Key') }}
            </x-buttons.button>
        </div>
    @endif

    {{-- Info Box --}}
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <p class="font-medium mb-1">{{ __('What is the APP_KEY?') }}</p>
                <p>{{ __('The application key is used by Laravel to encrypt cookies, sessions, and other sensitive data. It must be a 32-character random string encoded in base64.') }}</p>
            </div>
        </div>
    </div>
</div>

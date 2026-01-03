<div>
    @if($showBanner)
        <div class="bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800">
            <div class="px-4 py-3 mx-auto max-w-screen-2xl">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-800/50">
                            <iconify-icon icon="lucide:mail-warning" width="18" height="18" class="text-amber-600 dark:text-amber-400"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                {{ __('Email Not Verified') }}
                            </p>
                            <p class="text-xs text-amber-600 dark:text-amber-400">
                                {{ __('Please verify your email address to access all features. Check your inbox for a verification link.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($message)
                            <span class="text-xs px-2 py-1 rounded {{ $messageType === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-400' }}">
                                {{ $message }}
                            </span>
                        @endif

                        <button
                            wire:click="resendVerification"
                            wire:loading.attr="disabled"
                            wire:target="resendVerification"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span wire:loading.remove wire:target="resendVerification">
                                <iconify-icon icon="lucide:send" width="14" height="14"></iconify-icon>
                            </span>
                            <span wire:loading wire:target="resendVerification">
                                <iconify-icon icon="lucide:loader-2" width="14" height="14" class="animate-spin"></iconify-icon>
                            </span>
                            <span wire:loading.remove wire:target="resendVerification">{{ __('Resend Email') }}</span>
                            <span wire:loading wire:target="resendVerification">{{ __('Sending...') }}</span>
                        </button>

                        <button
                            wire:click="dismiss"
                            class="inline-flex items-center justify-center w-7 h-7 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-800/50 rounded-full transition-colors"
                            title="{{ __('Dismiss') }}"
                        >
                            <iconify-icon icon="lucide:x" width="16" height="16"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

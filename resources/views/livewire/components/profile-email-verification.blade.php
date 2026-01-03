<div class="mb-6">
    <div class="rounded-lg border-2 border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/20 overflow-hidden">
        <div class="p-5">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                {{-- Icon --}}
                <div class="shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-800/50">
                        <iconify-icon icon="lucide:mail-warning" width="24" height="24" class="text-amber-600 dark:text-amber-400"></iconify-icon>
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200">
                        {{ __('Email Verification Required') }}
                    </h3>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        {{ __('Your email address') }} <strong>{{ auth()->user()->email }}</strong> {{ __('is not verified yet.') }}
                    </p>
                    <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                        {{ __('Please verify your email to access all features. Check your inbox for a verification link, or click the button below to receive a new one.') }}
                    </p>

                    {{-- Message --}}
                    @if($message)
                        <div class="mt-3 p-3 rounded-md {{ $messageType === 'success' ? 'bg-green-100 dark:bg-green-800/30 border border-green-300 dark:border-green-700' : 'bg-red-100 dark:bg-red-800/30 border border-red-300 dark:border-red-700' }}">
                            <div class="flex items-center gap-2">
                                @if($messageType === 'success')
                                    <iconify-icon icon="lucide:check-circle" width="18" height="18" class="text-green-600 dark:text-green-400"></iconify-icon>
                                    <p class="text-sm font-medium text-green-700 dark:text-green-300">{{ $message }}</p>
                                @else
                                    <iconify-icon icon="lucide:alert-circle" width="18" height="18" class="text-red-600 dark:text-red-400"></iconify-icon>
                                    <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $message }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Action Button --}}
                    <div class="mt-4">
                        <button
                            wire:click="resendVerification"
                            wire:loading.attr="disabled"
                            wire:target="resendVerification"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span wire:loading.remove wire:target="resendVerification">
                                <iconify-icon icon="lucide:send" width="16" height="16"></iconify-icon>
                            </span>
                            <span wire:loading wire:target="resendVerification">
                                <iconify-icon icon="lucide:loader-2" width="16" height="16" class="animate-spin"></iconify-icon>
                            </span>
                            <span wire:loading.remove wire:target="resendVerification">{{ __('Resend Verification Email') }}</span>
                            <span wire:loading wire:target="resendVerification">{{ __('Sending...') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer with tips --}}
        <div class="px-5 py-3 bg-amber-100/50 dark:bg-amber-800/30 border-t border-amber-200 dark:border-amber-700">
            <div class="flex items-start gap-2 text-xs text-amber-700 dark:text-amber-400">
                <iconify-icon icon="lucide:info" width="14" height="14" class="mt-0.5 shrink-0"></iconify-icon>
                <p>
                    {{ __("Didn't receive the email? Check your spam folder, or make sure") }} <strong>{{ auth()->user()->email }}</strong> {{ __('is correct.') }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="text-center py-8">
    {{-- Success Icon --}}
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 mb-6">
        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>

    {{-- Title --}}
    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Installation Complete!') }}</h3>
    <p class="text-gray-500 dark:text-gray-400 mb-8">{{ __('Lara Dashboard has been successfully installed and configured.') }}</p>

    {{-- Summary Card --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 text-left max-w-md mx-auto mb-8">
        <h4 class="font-medium text-gray-900 dark:text-white mb-4">{{ __('Installation Summary') }}</h4>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">{{ __('Site Name') }}</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $siteName }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">{{ __('Admin Email') }}</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $adminEmail }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">{{ __('Admin Username') }}</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $adminUsername }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">{{ __('Database') }}</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ ucfirst($dbDriver) }}</dd>
            </div>
        </dl>
    </div>

    {{-- Next Steps --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 text-left max-w-md mx-auto">
        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Next Steps') }}
        </h4>
        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-2">
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                {{ __('Configure your site settings and theme') }}
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                {{ __('Enable modules you need') }}
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                {{ __('Start building your content') }}
            </li>
        </ul>
    </div>
</div>

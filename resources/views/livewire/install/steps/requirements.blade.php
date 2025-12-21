<div class="space-y-6">
    {{-- PHP Version --}}
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('PHP Version') }}</h3>
        <div class="flex items-center justify-between p-4 rounded-lg {{ $requirements['php']['passed'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
            <div class="flex items-center gap-3">
                @if ($requirements['php']['passed'])
                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @else
                    <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                @endif
                <div>
                    <span class="text-gray-900 dark:text-white font-medium">PHP {{ $requirements['php']['current'] }}</span>
                    <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">({{ __('Required') }}: >= {{ $requirements['php']['required'] }})</span>
                </div>
            </div>
        </div>
    </div>

    {{-- PHP Extensions --}}
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('PHP Extensions') }}</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach ($requirements['extensions'] as $extension => $passed)
                <div class="flex items-center gap-2 p-3 rounded-lg {{ $passed ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                    @if ($passed)
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    @endif
                    <span class="text-sm {{ $passed ? 'text-gray-700 dark:text-gray-300' : 'text-red-600 dark:text-red-400' }}">{{ $extension }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Writable Directories --}}
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Writable Directories') }}</h3>
        <div class="space-y-2">
            @foreach ($requirements['directories'] as $directory => $passed)
                <div class="flex items-center gap-2 p-3 rounded-lg {{ $passed ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                    @if ($passed)
                        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    @endif
                    <span class="text-sm font-mono {{ $passed ? 'text-gray-700 dark:text-gray-300' : 'text-red-600 dark:text-red-400' }}">{{ $directory }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ENV File --}}
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Environment File') }}</h3>
        <div class="flex items-center gap-2 p-3 rounded-lg {{ $requirements['env_writable'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
            @if ($requirements['env_writable'])
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('.env file is writable') }}</span>
            @else
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="text-sm text-red-600 dark:text-red-400">{{ __('.env file is not writable') }}</span>
            @endif
        </div>
    </div>
</div>

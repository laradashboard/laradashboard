@php
    $versionFile = base_path('version.json');
    $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
    $appVersion = $versionData['version'] ?? '0.0.0';
@endphp

<footer class="mt-auto border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
    <div class="px-4 py-3 sm:px-6">
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-xs text-gray-500 dark:text-gray-400">
            {{-- Version info --}}
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-medium">
                    <iconify-icon icon="lucide:package" class="text-sm"></iconify-icon>
                    v{{ $appVersion }}
                </span>
                <span class="text-gray-400 dark:text-gray-500">|</span>
                <span class="hidden sm:inline">Laravel {{ Illuminate\Foundation\Application::VERSION }}</span>
                <span class="sm:hidden">L{{ Illuminate\Foundation\Application::VERSION }}</span>
            </div>

            {{-- Right side - Credits & Links --}}
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline">{{ __('Made with') }}</span>
                <iconify-icon icon="lucide:heart" class="text-red-500 text-sm"></iconify-icon>
                <a href="https://laradashboard.com" target="_blank" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">
                    Lara Dashboard
                </a>
                <a href="https://github.com/laradashboard/laradashboard" target="_blank" title="GitHub" class="hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                    <iconify-icon icon="mdi:github" class="text-base"></iconify-icon>
                </a>
            </div>
        </div>
    </div>
</footer>

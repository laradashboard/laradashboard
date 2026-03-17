@php
    $activeTheme = $activeTheme ?? config('settings.active_theme', '');
@endphp

<x-card>
    <x-slot name="header">
        {{ __('Installed Themes') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Choose and activate a theme for your frontend website.') }}
    </x-slot>

    @if ($themes->isEmpty())
        <div class="text-center py-12">
            <iconify-icon icon="lucide:layout-template" class="text-5xl text-gray-300 dark:text-gray-600 mb-4" aria-hidden="true"></iconify-icon>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ __('No Themes Installed') }}
            </h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                {{ __('Install a theme module to enable frontend features. Theme modules have "theme": true in their module.json file.') }}
            </p>
            <a href="{{ route('admin.modules.index') }}" class="btn btn-primary">
                <iconify-icon icon="lucide:package" class="mr-2" aria-hidden="true"></iconify-icon>
                {{ __('Browse Modules') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($themes as $theme)
                @php
                    $isActive = $activeTheme === $theme['alias'];
                    $isEnabled = $theme['is_enabled'];
                @endphp
                <div class="relative group rounded-xl border-2 transition-all duration-200
                    {{ $isActive
                        ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-md'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-sm' }}">

                    {{-- Theme Preview / Screenshot --}}
                    <div class="relative aspect-video rounded-t-lg overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
                        @if ($theme['has_screenshot'])
                            <img
                                src="{{ $theme['screenshot_url'] }}"
                                alt="{{ __(':name theme preview', ['name' => $theme['name']]) }}"
                                class="w-full h-full object-cover"
                            >
                        @elseif (! empty($theme['homepage_url']) && $theme['is_enabled'])
                            <div class="w-full h-full relative">
                                <iframe
                                    src="{{ $theme['homepage_url'] }}"
                                    class="absolute top-0 left-0 border-0 pointer-events-none"
                                    style="width: 1440px; height: 900px; transform: scale(0.25); transform-origin: top left;"
                                    tabindex="-1"
                                    aria-hidden="true"
                                    loading="lazy"
                                    sandbox="allow-same-origin"
                                    title="{{ __(':name theme preview', ['name' => $theme['name']]) }}"
                                ></iframe>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500">
                                <iconify-icon icon="lucide:monitor" class="text-4xl mb-2" aria-hidden="true"></iconify-icon>
                                <span class="text-sm">{{ __('No preview available') }}</span>
                            </div>
                        @endif

                        {{-- Active Badge --}}
                        @if ($isActive)
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary text-white shadow-lg">
                                    <iconify-icon icon="lucide:check-circle" class="text-sm" aria-hidden="true"></iconify-icon>
                                    {{ __('Active') }}
                                </span>
                            </div>
                        @endif

                        {{-- Disabled Overlay --}}
                        @if (! $isEnabled)
                            <div class="absolute inset-0 bg-gray-900/50 flex items-center justify-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-500 text-white">
                                    <iconify-icon icon="lucide:alert-triangle" class="text-sm" aria-hidden="true"></iconify-icon>
                                    {{ __('Module Disabled') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Theme Info --}}
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $theme['name'] }}
                                </h3>
                                @if (! empty($theme['version']))
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        v{{ $theme['version'] }}
                                    </span>
                                @endif
                            </div>
                            @if ($isActive)
                                <iconify-icon icon="lucide:check-circle-2" class="text-xl text-primary shrink-0" aria-hidden="true"></iconify-icon>
                            @endif
                        </div>

                        @if (! empty($theme['description']))
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ $theme['description'] }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500 mb-4 italic">
                                {{ __('A frontend theme for your website.') }}
                            </p>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            @if ($isActive)
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-primary/10 text-primary dark:bg-primary/20 w-full justify-center">
                                    <iconify-icon icon="lucide:check" class="text-base" aria-hidden="true"></iconify-icon>
                                    {{ __('Active Theme') }}
                                </span>
                            @elseif ($isEnabled)
                                <form method="POST" action="{{ route('admin.theme.activate') }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="theme" value="{{ $theme['alias'] }}">
                                    <button type="submit" class="btn btn-primary w-full justify-center">
                                        <iconify-icon icon="lucide:check-circle" class="mr-1.5" aria-hidden="true"></iconify-icon>
                                        {{ __('Activate') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.modules.index') }}" class="btn btn-default w-full justify-center">
                                    <iconify-icon icon="lucide:power" class="mr-1.5" aria-hidden="true"></iconify-icon>
                                    {{ __('Enable Module First') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($themes->count() === 1 && ! empty($activeTheme))
            <div class="mt-4 flex items-start gap-3 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">
                <iconify-icon icon="lucide:info" class="text-lg mt-0.5 shrink-0" aria-hidden="true"></iconify-icon>
                <p class="text-sm">
                    {{ __('You have one theme installed and it has been automatically activated. Install additional theme modules to switch between themes.') }}
                </p>
            </div>
        @endif
    @endif
</x-card>

<div wire:init="loadModules" x-data="{ showDetail: false, selectedModule: null, modalInstalling: false }"
     x-on:module-installed.window="modalInstalling = false; showDetail = false; setTimeout(() => window.location.reload(), 500)"
     x-on:module-install-failed.window="modalInstalling = false">
    @if (! $loaded)
        {{-- Loading skeleton --}}
        <div class="flex justify-center items-center py-16">
            <iconify-icon icon="lucide:loader-2" class="animate-spin text-3xl text-gray-400"></iconify-icon>
            <span class="ml-3 text-gray-500 dark:text-gray-400">{{ __('Loading marketplace...') }}</span>
        </div>
    @elseif ($apiError)
        {{-- Error state --}}
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <iconify-icon icon="lucide:cloud-off" class="text-5xl text-gray-400 mb-4"></iconify-icon>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('Unable to load marketplace') }}</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4 max-w-md">{{ $apiError }}</p>
            <button wire:click="retry" class="btn-primary">
                <iconify-icon icon="lucide:refresh-cw" class="mr-2"></iconify-icon>
                {{ __('Try Again') }}
            </button>
        </div>
    @else
        {{-- Search and Filters --}}
        <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="relative w-full sm:w-80">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></iconify-icon>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search modules...') }}"
                    class="form-control pl-10 w-full"
                    aria-label="{{ __('Search marketplace modules') }}"
                />
            </div>

            <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('Filter by module type') }}">
                @php
                    $filters = [
                        '' => __('All'),
                        'free' => __('Free'),
                        'freemium' => __('Freemium'),
                        'pro' => __('Pro'),
                    ];
                @endphp
                @foreach ($filters as $value => $label)
                    <button
                        wire:click="setTypeFilter('{{ $value ?: 'all' }}')"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                            {{ $typeFilter === $value
                                ? 'bg-primary text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}"
                        aria-pressed="{{ $typeFilter === $value ? 'true' : 'false' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        @if (count($modules) === 0)
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <iconify-icon icon="lucide:package-search" class="text-5xl text-gray-400 mb-4"></iconify-icon>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('No modules found') }}</h3>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ $search ? __('Try adjusting your search or filters.') : __('No modules are available in the marketplace yet.') }}
                </p>
            </div>
        @else
            {{-- Module Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($modules as $index => $module)
                    @php
                        $moduleSlug = $module['slug'] ?? strtolower($module['name'] ?? '');
                        $moduleName = $module['name'] ?? $module['title'] ?? 'Unknown';
                        $moduleDescription = $module['description'] ?? '';
                        $moduleVersion = $module['version'] ?? '1.0.0';
                        $moduleType = $module['module_type'] ?? 'free';
                        $isFree = $moduleType === 'free';
                        $isInstalled = in_array(strtolower($moduleSlug), $installedSlugs);
                        $isInstalling = $installingSlug === $moduleSlug;

                        $iconsRaw = $module['icons'] ?? null;
                        $versionJson = $module['version_json'] ?? [];
                        $iconString = $module['icon'] ?? ($versionJson['icon'] ?? null);
                        $moduleBannerUrl = null;
                        $moduleIconUrl = null;

                        // Decode JSON strings from the API (icons/banner may arrive as JSON-encoded strings)
                        if (is_string($iconsRaw) && str_starts_with(trim($iconsRaw), '{')) {
                            $iconsRaw = json_decode($iconsRaw, true) ?: $iconsRaw;
                        }
                        if (is_array($iconsRaw)) {
                            $moduleIconUrl = $iconsRaw['default'] ?? $iconsRaw['logo'] ?? $iconsRaw['1x'] ?? null;
                        } elseif (is_string($iconsRaw)) {
                            $moduleIconUrl = $iconsRaw;
                        }

                        $bannerRaw = $module['banner'] ?? $module['banner_image'] ?? null;
                        if (is_string($bannerRaw) && (str_starts_with(trim($bannerRaw), '{') || str_starts_with(trim($bannerRaw), '['))) {
                            $bannerRaw = json_decode($bannerRaw, true) ?: $bannerRaw;
                        }
                        if (is_array($bannerRaw)) {
                            $moduleBannerUrl = $bannerRaw['default'] ?? $bannerRaw['url'] ?? ($bannerRaw[0] ?? null);
                        } elseif (is_string($bannerRaw)) {
                            $moduleBannerUrl = $bannerRaw;
                        }

                        if ($moduleIconUrl && !str_starts_with($moduleIconUrl, 'http')) {
                            $moduleIconUrl = $marketplaceUrl . $moduleIconUrl;
                        }
                        if ($moduleBannerUrl && !str_starts_with($moduleBannerUrl, 'http')) {
                            $moduleBannerUrl = $marketplaceUrl . $moduleBannerUrl;
                        }

                        // Screenshots
                        $screenshots = $module['screenshots'] ?? [];
                        if (is_array($screenshots)) {
                            $screenshots = array_map(function ($s) use ($marketplaceUrl) {
                                return (is_string($s) && !str_starts_with($s, 'http')) ? $marketplaceUrl . $s : $s;
                            }, $screenshots);
                        } else {
                            $screenshots = [];
                        }

                        // Module detail data for the modal
                        $moduleDetail = [
                            'name' => $moduleName,
                            'slug' => $moduleSlug,
                            'description' => $moduleDescription,
                            'version' => $moduleVersion,
                            'type' => $moduleType,
                            'isFree' => $isFree,
                            'isInstalled' => $isInstalled,
                            'bannerUrl' => $moduleBannerUrl,
                            'iconUrl' => $moduleIconUrl,
                            'iconString' => $iconString,
                            'screenshots' => $screenshots,
                            'minRequired' => $module['min_laradashboard_required'] ?? '1.0.0',
                            'documentationUrl' => $module['documentation_url'] ?? null,
                            'demoUrl' => $module['demo_url'] ?? null,
                            'issuesUrl' => $module['issues_url'] ?? null,
                            'websiteUrl' => $module['website_url'] ?? null,
                            'isFeatured' => $module['is_featured'] ?? false,
                            'createdAt' => $module['created_at'] ?? null,
                            'updatedAt' => $module['updated_at'] ?? null,
                        ];
                    @endphp

                    <div wire:key="marketplace-module-{{ $moduleSlug }}"
                         @click="selectedModule = {{ json_encode($moduleDetail) }}; showDetail = true"
                         class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col transition-shadow hover:shadow-md cursor-pointer">
                        {{-- Banner / Icon area --}}
                        <div class="h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center relative">
                            @if ($moduleBannerUrl)
                                <img src="{{ $moduleBannerUrl }}" alt="" class="w-full h-full object-cover" aria-hidden="true" />
                            @elseif ($moduleIconUrl)
                                <img src="{{ $moduleIconUrl }}" alt="" class="w-16 h-16 object-contain" aria-hidden="true" />
                            @elseif ($iconString)
                                <iconify-icon icon="{{ $iconString }}" class="text-5xl text-gray-400 dark:text-gray-500"></iconify-icon>
                            @else
                                <iconify-icon icon="lucide:package" class="text-5xl text-gray-400 dark:text-gray-500"></iconify-icon>
                            @endif

                            <span class="absolute top-2 right-2 px-2 py-0.5 rounded text-xs font-semibold
                                @if ($moduleType === 'free') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                @elseif ($moduleType === 'pro') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300
                                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                @endif">
                                {{ ucfirst($moduleType) }}
                            </span>
                        </div>

                        {{-- Content --}}
                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1 truncate" title="{{ $moduleName }}">
                                {{ $moduleName }}
                            </h3>

                            @php
                                // Remove headings from preview text so it reads as a clean excerpt
                                $previewText = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', '', $moduleDescription);
                                $previewText = \Illuminate\Support\Str::limit(strip_tags($previewText), 120);
                            @endphp
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2 flex-1">
                                {{ $previewText }}
                            </p>

                            <div class="flex items-center justify-between mt-auto">
                                <span class="text-xs text-gray-400 dark:text-gray-500">v{{ $moduleVersion }}</span>

                                @if ($isInstalled)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-default">
                                        <iconify-icon icon="lucide:check" class="mr-1"></iconify-icon>
                                        {{ __('Installed') }}
                                    </span>
                                @elseif ($isFree)
                                    <button
                                        wire:click.stop="installModule('{{ $moduleSlug }}', '{{ $moduleVersion }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="installModule('{{ $moduleSlug }}', '{{ $moduleVersion }}')"
                                        @if ($isInstalling) disabled @endif
                                        class="text-sm px-3 py-1.5 inline-flex items-center {{ $isInstalling ? 'btn-default cursor-wait' : 'btn-primary' }}"
                                    >
                                        <span wire:loading.remove wire:target="installModule('{{ $moduleSlug }}', '{{ $moduleVersion }}')"
                                            class="inline-flex items-center {{ $isInstalling ? 'hidden' : '' }}">
                                            <iconify-icon icon="lucide:download" class="mr-1"></iconify-icon>
                                            {{ __('Install') }}
                                        </span>
                                        <span wire:loading wire:target="installModule('{{ $moduleSlug }}', '{{ $moduleVersion }}')"
                                            class="inline-flex items-center">
                                            <iconify-icon icon="lucide:loader-2" class="mr-1 animate-spin"></iconify-icon>
                                            {{ __('Installing...') }}
                                        </span>
                                        @if ($isInstalling)
                                            <span wire:loading.remove wire:target="installModule('{{ $moduleSlug }}', '{{ $moduleVersion }}')"
                                                class="inline-flex items-center">
                                                <iconify-icon icon="lucide:loader-2" class="mr-1 animate-spin"></iconify-icon>
                                                {{ __('Installing...') }}
                                            </span>
                                        @endif
                                    </button>
                                @else
                                    <a
                                        href="{{ $marketplaceUrl }}/modules/{{ $moduleSlug }}"
                                        target="_blank"
                                        rel="noopener"
                                        @click.stop
                                        class="btn-default text-sm px-3 py-1.5 inline-flex items-center"
                                    >
                                        <iconify-icon icon="lucide:external-link" class="mr-1"></iconify-icon>
                                        @if ($moduleType === 'pro')
                                            {{ __('Purchase') }}
                                        @else
                                            {{ __('Get Module') }}
                                        @endif
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if (($meta['last_page'] ?? 1) > 1)
                <div class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Page :current of :last', ['current' => $meta['current_page'] ?? 1, 'last' => $meta['last_page'] ?? 1]) }}
                        @if (isset($meta['total']))
                            <span class="ml-1">({{ $meta['total'] }} {{ __('modules') }})</span>
                        @endif
                    </p>

                    <div class="flex gap-2">
                        <button
                            wire:click="previousPage"
                            @if (($meta['current_page'] ?? 1) <= 1) disabled @endif
                            class="btn-default text-sm px-3 py-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <iconify-icon icon="lucide:chevron-left" class="mr-1"></iconify-icon>
                            {{ __('Previous') }}
                        </button>
                        <button
                            wire:click="nextPage"
                            @if (($meta['current_page'] ?? 1) >= ($meta['last_page'] ?? 1)) disabled @endif
                            class="btn-default text-sm px-3 py-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ __('Next') }}
                            <iconify-icon icon="lucide:chevron-right" class="ml-1"></iconify-icon>
                        </button>
                    </div>
                </div>
            @endif
        @endif
    @endif

    {{-- Module Detail Modal --}}
    <template x-teleport="body">
        <div
            x-cloak
            x-show="showDetail"
            x-transition.opacity.duration.200ms
            x-trap.inert.noscroll="showDetail"
            x-on:keydown.esc.window="showDetail = false"
            x-on:click.self="showDetail = false"
            class="fixed inset-0 z-50 flex items-start justify-center bg-black/20 p-4 pt-16 backdrop-blur-md overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="module-detail-title"
        >
            <div
                x-show="showDetail"
                x-transition:enter="transition ease-out duration-200 delay-100"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="w-full max-w-3xl rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl mb-8"
            >
                <template x-if="selectedModule">
                    <div>
                        {{-- Header with banner --}}
                        <div class="relative">
                            <div class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center rounded-t-lg overflow-hidden">
                                <template x-if="selectedModule.bannerUrl">
                                    <img :src="selectedModule.bannerUrl" alt="" class="w-full h-full object-cover" aria-hidden="true" />
                                </template>
                                <template x-if="!selectedModule.bannerUrl && selectedModule.iconUrl">
                                    <img :src="selectedModule.iconUrl" alt="" class="w-20 h-20 object-contain" aria-hidden="true" />
                                </template>
                                <template x-if="!selectedModule.bannerUrl && !selectedModule.iconUrl && selectedModule.iconString">
                                    <iconify-icon :icon="selectedModule.iconString" class="text-6xl text-gray-400 dark:text-gray-500"></iconify-icon>
                                </template>
                                <template x-if="!selectedModule.bannerUrl && !selectedModule.iconUrl && !selectedModule.iconString">
                                    <iconify-icon icon="lucide:package" class="text-6xl text-gray-400 dark:text-gray-500"></iconify-icon>
                                </template>
                            </div>

                            {{-- Close button --}}
                            <button
                                x-on:click="showDetail = false"
                                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-full text-gray-500 hover:text-gray-700 hover:bg-white dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors shadow-sm"
                                aria-label="{{ __('Close') }}"
                            >
                                <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
                            </button>

                            {{-- Type badge --}}
                            <span
                                x-text="selectedModule.type.charAt(0).toUpperCase() + selectedModule.type.slice(1)"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': selectedModule.type === 'free',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': selectedModule.type === 'pro',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': selectedModule.type !== 'free' && selectedModule.type !== 'pro',
                                }"
                                class="absolute top-3 left-3 px-2.5 py-1 rounded text-xs font-semibold"
                            ></span>
                        </div>

                        {{-- Module info --}}
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h2 id="module-detail-title" class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedModule.name"></h2>
                                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center">
                                            <iconify-icon icon="lucide:tag" class="mr-1"></iconify-icon>
                                            v<span x-text="selectedModule.version"></span>
                                        </span>
                                        <span class="inline-flex items-center">
                                            <iconify-icon icon="lucide:shield-check" class="mr-1"></iconify-icon>
                                            {{ __('Requires') }} v<span x-text="selectedModule.minRequired"></span>+
                                        </span>
                                        <template x-if="selectedModule.isFeatured">
                                            <span class="inline-flex items-center text-amber-500">
                                                <iconify-icon icon="lucide:star" class="mr-1"></iconify-icon>
                                                {{ __('Featured') }}
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Action button in modal --}}
                                <div x-data>
                                    <template x-if="selectedModule.isInstalled">
                                        <span class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            <iconify-icon icon="lucide:check" class="mr-1.5"></iconify-icon>
                                            {{ __('Installed') }}
                                        </span>
                                    </template>
                                    <template x-if="!selectedModule.isInstalled && selectedModule.isFree">
                                        <button
                                            x-on:click="modalInstalling = true; $wire.installModule(selectedModule.slug, selectedModule.version)"
                                            :disabled="modalInstalling"
                                            :class="modalInstalling ? 'btn-default cursor-wait' : 'btn-primary'"
                                            class="px-4 py-2 inline-flex items-center"
                                        >
                                            <template x-if="!modalInstalling">
                                                <span class="inline-flex items-center">
                                                    <iconify-icon icon="lucide:download" class="mr-1.5"></iconify-icon>
                                                    {{ __('Install Module') }}
                                                </span>
                                            </template>
                                            <template x-if="modalInstalling">
                                                <span class="inline-flex items-center">
                                                    <iconify-icon icon="lucide:loader-2" class="mr-1.5 animate-spin"></iconify-icon>
                                                    {{ __('Installing...') }}
                                                </span>
                                            </template>
                                        </button>
                                    </template>
                                    <template x-if="!selectedModule.isInstalled && !selectedModule.isFree">
                                        <a
                                            :href="'{{ $marketplaceUrl }}/modules/' + selectedModule.slug"
                                            target="_blank"
                                            rel="noopener"
                                            class="btn-primary px-4 py-2 inline-flex items-center"
                                        >
                                            <iconify-icon icon="lucide:external-link" class="mr-1.5"></iconify-icon>
                                            <span x-text="selectedModule.type === 'pro' ? '{{ __('Purchase') }}' : '{{ __('Get Module') }}'"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>

                            {{-- Links bar --}}
                            <div class="flex flex-wrap gap-3 mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <a
                                    :href="'{{ $marketplaceUrl }}/modules/' + selectedModule.slug"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center text-sm text-primary hover:underline"
                                >
                                    <iconify-icon icon="lucide:store" class="mr-1"></iconify-icon>
                                    {{ __('Marketplace Page') }}
                                </a>
                                <template x-if="selectedModule.documentationUrl">
                                    <a
                                        :href="selectedModule.documentationUrl"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center text-sm text-primary hover:underline"
                                    >
                                        <iconify-icon icon="lucide:book-open" class="mr-1"></iconify-icon>
                                        {{ __('Documentation') }}
                                    </a>
                                </template>
                                <template x-if="selectedModule.demoUrl">
                                    <a
                                        :href="selectedModule.demoUrl"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center text-sm text-primary hover:underline"
                                    >
                                        <iconify-icon icon="lucide:play-circle" class="mr-1"></iconify-icon>
                                        {{ __('Live Demo') }}
                                    </a>
                                </template>
                                <template x-if="selectedModule.issuesUrl">
                                    <a
                                        :href="selectedModule.issuesUrl"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center text-sm text-primary hover:underline"
                                    >
                                        <iconify-icon icon="lucide:bug" class="mr-1"></iconify-icon>
                                        {{ __('Report Issue') }}
                                    </a>
                                </template>
                            </div>

                            {{-- Description --}}
                            <div class="mb-5">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ __('Description') }}</h3>
                                <div
                                    class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 max-h-64 overflow-y-auto prose-h1:text-base prose-h1:font-semibold prose-h2:text-sm prose-h2:font-semibold prose-h3:text-sm prose-h3:font-medium prose-p:text-sm prose-li:text-sm"
                                    x-html="selectedModule.description"
                                ></div>
                            </div>

                            {{-- Screenshots --}}
                            <template x-if="selectedModule.screenshots && selectedModule.screenshots.length > 0">
                                <div class="mb-5">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ __('Screenshots') }}</h3>
                                    <div class="grid grid-cols-2 gap-3">
                                        <template x-for="(screenshot, i) in selectedModule.screenshots" :key="i">
                                            <a :href="screenshot" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
                                                <img :src="screenshot" alt="" class="w-full h-32 object-cover" loading="lazy" />
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Meta info --}}
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('Version') }}</span>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="'v' + selectedModule.version"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('Requires Core') }}</span>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="'v' + selectedModule.minRequired + '+'"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('Type') }}</span>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="selectedModule.type.charAt(0).toUpperCase() + selectedModule.type.slice(1)"></p>
                                    </div>
                                    <template x-if="selectedModule.updatedAt">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">{{ __('Last Updated') }}</span>
                                            <p class="font-medium text-gray-900 dark:text-white" x-text="new Date(selectedModule.updatedAt).toLocaleDateString()"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

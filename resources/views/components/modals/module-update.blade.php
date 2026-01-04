@props([
    'id' => 'module-update-modal',
    'module',
    'updateInfo',
    'hasValidLicense' => true,
    'requiresLicense' => false,
    'modalTrigger' => 'updateModalOpen',
])

@php
    $moduleService = app(\App\Services\Modules\ModuleService::class);
    $moduleSlug = $moduleService->normalizeModuleName($module->name);
    $canUpdate = !$requiresLicense || $hasValidLicense;
@endphp

<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $modalTrigger }}"
        x-transition.opacity.duration.200ms
        x-trap.inert.noscroll="{{ $modalTrigger }}"
        x-on:keydown.esc.window="{{ $modalTrigger }} = false"
        x-on:click.self="{{ $modalTrigger }} = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}-title"
    >
        <div
            x-show="{{ $modalTrigger }}"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-w-lg w-full flex-col gap-4 overflow-hidden rounded-md border border-outline border-gray-100 dark:border-gray-800 bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 p-2">
                        <iconify-icon icon="lucide:arrow-up-circle" class="text-xl"></iconify-icon>
                    </div>
                    <h3 id="{{ $id }}-title" class="font-semibold tracking-wide text-gray-700 dark:text-white flex items-center gap-2">
                        {{ __('Update Available') }}
                        @if($requiresLicense)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-800/30 dark:text-purple-300">
                                {{ __('Pro') }}
                            </span>
                        @endif
                    </h3>
                </div>
                <button
                    x-on:click="{{ $modalTrigger }} = false"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white"
                >
                    <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
                </button>
            </div>

            {{-- Content --}}
            <div class="px-4 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-800">
                        <iconify-icon icon="{{ $module->icon ?? 'lucide:box' }}" class="text-2xl text-gray-600 dark:text-gray-400"></iconify-icon>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $module->title }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $module->name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Current Version') }}</span>
                        <p class="font-medium text-gray-700 dark:text-gray-300">v{{ $updateInfo['current_version'] ?? $module->version }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('New Version') }}</span>
                        <p class="font-medium text-blue-600 dark:text-blue-400">v{{ $updateInfo['latest_version'] }}</p>
                    </div>
                </div>

                @if(!empty($updateInfo['changelog']))
                    <div>
                        <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Changelog') }}</h5>
                        <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg max-h-32 overflow-y-auto">
                            {!! nl2br(e($updateInfo['changelog'])) !!}
                        </div>
                    </div>
                @endif

                @if(!empty($updateInfo['required_core']) || !empty($updateInfo['required_php']))
                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        @if(!empty($updateInfo['required_core']))
                            <p>{{ __('Requires LaraDashboard') }}: v{{ $updateInfo['required_core'] }}+</p>
                        @endif
                        @if(!empty($updateInfo['required_php']))
                            <p>{{ __('Requires PHP') }}: v{{ $updateInfo['required_php'] }}+</p>
                        @endif
                    </div>
                @endif

                @if($requiresLicense && !$hasValidLicense)
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="flex items-start gap-2">
                            <iconify-icon icon="lucide:key" class="text-purple-600 dark:text-purple-400 mt-0.5"></iconify-icon>
                            <div>
                                <p class="text-sm font-medium text-purple-700 dark:text-purple-300">
                                    {{ __('License Required') }}
                                </p>
                                <p class="text-sm text-purple-600 dark:text-purple-400 mt-1">
                                    {{ __('This is a pro module. You need to activate your license before you can download updates. If you already have a license, please activate it on this domain.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                        <div class="flex items-start gap-2">
                            <iconify-icon icon="lucide:alert-triangle" class="text-amber-600 dark:text-amber-400 mt-0.5"></iconify-icon>
                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                {{ __('It is recommended to backup your data before updating. The module will be temporarily disabled during the update process.') }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
                <button
                    type="button"
                    x-on:click="{{ $modalTrigger }} = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                >
                    {{ __('Cancel') }}
                </button>
                @if($canUpdate)
                    <button
                        type="button"
                        wire:click="updateModule('{{ $module->name }}')"
                        x-on:click="{{ $modalTrigger }} = false"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800"
                    >
                        <iconify-icon icon="lucide:download" class="text-base"></iconify-icon>
                        {{ __('Update Now') }}
                    </button>
                @else
                    <button
                        type="button"
                        x-on:click="{{ $modalTrigger }} = false; $dispatch('open-license-modal-{{ $moduleSlug }}')"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-300 dark:focus:ring-purple-800"
                    >
                        <iconify-icon icon="lucide:key" class="text-base"></iconify-icon>
                        {{ __('Activate License') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</template>

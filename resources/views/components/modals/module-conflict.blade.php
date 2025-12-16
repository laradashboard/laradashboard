@props([
    'id' => 'module-conflict-modal',
    'modalTrigger' => 'showConflictModal',
    'conflictDataVar' => 'conflictData',
    'isReplacingVar' => 'isReplacing',
    'onReplace' => 'replaceModule()',
    'onCancel' => 'cancelReplacement()',
])

<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $modalTrigger }}"
        x-transition.opacity.duration.200ms
        x-trap.inert.noscroll="{{ $modalTrigger }}"
        x-on:keydown.esc.window="{{ $onCancel }}"
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
            @click.away="{{ $onCancel }}"
            class="flex max-w-2xl w-full flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl"
        >
            <!-- Header -->
            <div class="flex items-center gap-3 px-6 py-4 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-700">
                <div class="shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <iconify-icon icon="lucide:alert-triangle" class="text-xl text-amber-600 dark:text-amber-400"></iconify-icon>
                </div>
                <div class="flex-1">
                    <h3 id="{{ $id }}-title" class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Module Already Exists') }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('This module is already installed. Would you like to replace it?') }}
                    </p>
                </div>
                <button
                    x-on:click="{{ $onCancel }}"
                    :disabled="{{ $isReplacingVar }}"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            <!-- Comparison Table -->
            <div class="px-6 py-4" x-show="{{ $conflictDataVar }}">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400"></th>
                                <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-white">{{ __('Current') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-white">{{ __('Uploaded') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ __('Module name') }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="{{ $conflictDataVar }}?.current?.name || '-'"></td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="{{ $conflictDataVar }}?.uploaded?.name || '-'"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ __('Version') }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="{{ $conflictDataVar }}?.current?.version || '-'"></td>
                                <td class="px-4 py-3" :class="{{ $conflictDataVar }}?.uploaded?.version !== {{ $conflictDataVar }}?.current?.version ? 'text-primary font-medium' : 'text-gray-900 dark:text-white'" x-text="{{ $conflictDataVar }}?.uploaded?.version || '-'"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ __('Author') }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="{{ $conflictDataVar }}?.current?.author || '-'"></td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="{{ $conflictDataVar }}?.uploaded?.author || '-'"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 text-xs" x-text="{{ $conflictDataVar }}?.current?.description || '-'"></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 text-xs" x-text="{{ $conflictDataVar }}?.uploaded?.description || '-'"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Warning Message -->
                <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                    <p class="text-sm text-amber-700 dark:text-amber-400 flex items-start gap-2">
                        <iconify-icon icon="lucide:info" class="text-lg shrink-0 mt-0.5"></iconify-icon>
                        <span>{{ __('Replacing the current module will delete all existing files. If the module was active, it will be re-activated after replacement. Make sure to backup your data first.') }}</span>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
                <button
                    x-on:click="{{ $onCancel }}"
                    :disabled="{{ $isReplacingVar }}"
                    class="btn-default"
                >
                    {{ __('Cancel and go back') }}
                </button>
                <button
                    x-on:click="{{ $onReplace }}"
                    :disabled="{{ $isReplacingVar }}"
                    class="btn-primary"
                >
                    <iconify-icon x-show="{{ $isReplacingVar }}" icon="lucide:loader-2" class="mr-1 animate-spin"></iconify-icon>
                    <iconify-icon x-show="!{{ $isReplacingVar }}" icon="lucide:refresh-cw" class="mr-1"></iconify-icon>
                    <span x-text="{{ $isReplacingVar }} ? '{{ __('Replacing...') }}' : '{{ __('Replace current with uploaded') }}'"></span>
                </button>
            </div>
        </div>
    </div>
</template>

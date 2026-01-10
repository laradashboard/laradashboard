@props([
    'id' => 'create-backup-modal',
    'modalTrigger' => 'showBackupModal',
])

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
            class="flex w-full max-w-lg flex-col gap-4 overflow-hidden rounded-lg border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 p-2">
                        <iconify-icon icon="lucide:archive" class="text-xl text-indigo-600 dark:text-indigo-400"></iconify-icon>
                    </div>
                    <h3 id="{{ $id }}-title" class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('Create Backup') }}
                    </h3>
                </div>
                <button
                    x-on:click="{{ $modalTrigger }} = false"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-md p-1.5 dark:hover:bg-gray-700 dark:hover:text-white"
                >
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Choose what to include in your backup. Larger backups take more time and storage space.') }}
                </p>

                <div class="space-y-3">
                    {{-- Backup Type Options --}}
                    <label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                           :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': backupType === 'core' }">
                        <input type="radio" name="backup_type" value="core" x-model="backupType"
                               class="mt-0.5 w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div class="flex-1">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ __('Core Only') }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('App, config, routes, views, database migrations. Smallest size.') }}</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                           :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': backupType === 'core_with_modules' }">
                        <input type="radio" name="backup_type" value="core_with_modules" x-model="backupType"
                               class="mt-0.5 w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div class="flex-1">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ __('Core + Modules') }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('Includes all installed modules. Recommended for most cases.') }}</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                           :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': backupType === 'core_with_uploads' }">
                        <input type="radio" name="backup_type" value="core_with_uploads" x-model="backupType"
                               class="mt-0.5 w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div class="flex-1">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ __('Core + Uploads') }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('Includes uploaded media files. May be large.') }}</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                           :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': backupType === 'full' }">
                        <input type="radio" name="backup_type" value="full" x-model="backupType"
                               class="mt-0.5 w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div class="flex-1">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ __('Full Backup') }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('Core, modules, and uploads. Complete site backup. Largest size.') }}</span>
                        </div>
                    </label>
                </div>

                {{-- Include Database Option --}}
                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="includeDatabase"
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Include database dump') }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('Export SQL dump of the database') }}</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700 px-5 py-4">
                <button
                    type="button"
                    x-on:click="{{ $modalTrigger }} = false"
                    class="btn btn-secondary"
                >
                    {{ __('Cancel') }}
                </button>
                <button
                    type="button"
                    x-on:click="startBackup()"
                    x-bind:disabled="isCreatingBackup"
                    class="btn btn-primary flex items-center gap-2"
                >
                    <template x-if="!isCreatingBackup">
                        <span class="flex items-center gap-2">
                            <iconify-icon icon="lucide:archive" class="text-lg"></iconify-icon>
                            {{ __('Create Backup') }}
                        </span>
                    </template>
                    <template x-if="isCreatingBackup">
                        <span class="flex items-center gap-2">
                            <iconify-icon icon="lucide:loader-2" class="text-lg animate-spin"></iconify-icon>
                            {{ __('Creating...') }}
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </div>
</template>

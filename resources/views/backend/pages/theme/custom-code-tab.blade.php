<!-- Custom CSS & JS Section -->
<x-card>
    <x-slot name="header">
        {{ __('Custom CSS & JavaScript') }}
    </x-slot>
    <div class="space-y-4">
        <div>
            <label for="global_custom_css" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Global Custom CSS') }}
            </label>
            <textarea id="global_custom_css" name="global_custom_css" rows="6"
                class="form-control h-16"
                placeholder=".my-class { color: red; }">{{ config('settings.global_custom_css') }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                {{ __('Add custom CSS that will be applied to all pages') }}
            </p>
        </div>

        <div>
            <label for="global_custom_js" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Global Custom JavaScript') }}
            </label>
            <textarea id="global_custom_js" name="global_custom_js" rows="6"
                class="form-control h-16"
                placeholder="document.addEventListener('DOMContentLoaded', function() { /* Your code */ });">{{ config('settings.global_custom_js') }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                {{ __('Add custom JavaScript that will be loaded on all pages') }}
            </p>
        </div>
    </div>
</x-card>

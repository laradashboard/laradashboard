<div class="space-y-6" x-data="{
    siteName: $wire.entangle('siteName'),
    primaryColor: $wire.entangle('primaryColor')
}">
    {{-- Site Name --}}
    <div>
        <x-inputs.input
            name="siteName"
            label="{{ __('Site Name') }}"
            x-model="siteName"
            wire:model="siteName"
            :value="$siteName"
            placeholder="{{ __('My Awesome Site') }}"
            hint="{{ __('This will be displayed in the browser title and throughout the application') }}"
            required
        />
    </div>

    {{-- Primary Color --}}
    <div>
        <label class="form-label">{{ __('Brand Primary Color') }}</label>
        <div class="flex items-center gap-3">
            <input
                type="color"
                x-model="primaryColor"
                wire:model="primaryColor"
                class="w-12 h-10 rounded cursor-pointer border border-gray-200 dark:border-gray-700"
            />
            <input
                type="text"
                x-model="primaryColor"
                wire:model="primaryColor"
                class="form-control w-32 font-mono text-sm"
                placeholder="#635bff"
            />
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Click to pick a color') }}</span>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ __('This color will be used for buttons, links, and accent elements') }}</p>
    </div>

    {{-- Live Preview --}}
    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('Preview') }}</p>
        <div class="flex items-center gap-3 mb-4">
            <div
                class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                :style="{ backgroundColor: primaryColor }"
            >
                <span class="text-white font-bold text-lg" x-text="(siteName || 'L').charAt(0).toUpperCase()"></span>
            </div>
            <span class="text-lg font-semibold text-gray-900 dark:text-white" x-text="siteName || '{{ __('Lara Dashboard') }}'"></span>
        </div>

        {{-- Button Preview --}}
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-white rounded-md transition-colors"
                :style="{ backgroundColor: primaryColor }"
            >
                {{ __('Primary Button') }}
            </button>
            <span
                class="text-sm font-medium cursor-pointer"
                :style="{ color: primaryColor }"
            >
                {{ __('Link Text') }}
            </span>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <p class="font-medium mb-1">{{ __('Additional Settings') }}</p>
                <p>{{ __('You can configure more settings like logo, secondary color, and other preferences from the admin panel after installation.') }}</p>
            </div>
        </div>
    </div>
</div>

@php
    $licenseService = app(\App\Services\LicenseVerificationService::class);
    $moduleService = app(\App\Services\Modules\ModuleService::class);

    $requiresLicense = $updateInfo['requires_license'] ?? false;
    $moduleSlug = $moduleService->normalizeModuleName($module->name);
    $storedLicense = $requiresLicense ? $licenseService->getStoredLicense($moduleSlug) : null;
    $hasValidLicense = $storedLicense && !empty($storedLicense['license_key']);
    $canUpdate = !$requiresLicense || $hasValidLicense;
@endphp

<div
    x-data="{ updateModalOpen: false, licenseModalOpen: false }"
    x-on:open-update-modal-{{ $module->name }}.window="updateModalOpen = true"
    x-on:open-license-modal-{{ $moduleSlug }}.window="licenseModalOpen = true"
    class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 dark:border-amber-500 px-4 py-3 md:mx-10 mb-3"
>
    <div class="flex items-center gap-3">
        <iconify-icon icon="lucide:alert-circle" class="text-amber-600 dark:text-amber-400 text-lg shrink-0"></iconify-icon>
        <p class="text-sm text-amber-800 dark:text-amber-200">
            {{ __('There is a new version of :name available.', ['name' => $module->title]) }}
            @if($requiresLicense)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-800/30 dark:text-purple-300 ml-1">
                    {{ __('Pro') }}
                </span>
            @endif
            <button
                type="button"
                class="text-amber-700 dark:text-amber-300 underline hover:no-underline font-medium"
                x-on:click="updateModalOpen = true"
            >
                {{ __('View version :version details', ['version' => $updateInfo['latest_version']]) }}
            </button>
            {{ __('or') }}
            @if($canUpdate)
                <button
                    type="button"
                    wire:click="updateModule('{{ $module->name }}')"
                    wire:loading.attr="disabled"
                    wire:target="updateModule('{{ $module->name }}')"
                    class="text-amber-700 dark:text-amber-300 underline hover:no-underline font-medium"
                >
                    <span wire:loading.remove wire:target="updateModule('{{ $module->name }}')">
                        {{ __('update now') }}
                    </span>
                    <span wire:loading wire:target="updateModule('{{ $module->name }}')">
                        {{ __('updating...') }}
                    </span>
                </button>.
            @else
                <button
                    type="button"
                    x-on:click="licenseModalOpen = true"
                    class="text-purple-700 dark:text-purple-300 underline hover:no-underline font-medium"
                >
                    {{ __('activate license to update') }}
                </button>.
                <span class="text-amber-600 dark:text-amber-400 text-xs">
                    ({{ __('License required for pro module updates') }})
                </span>
            @endif
        </p>
    </div>

    {{-- Update Details Modal --}}
    <x-modals.module-update
        id="update-modal-{{ $module->name }}"
        :module="$module"
        :updateInfo="$updateInfo"
        :hasValidLicense="$hasValidLicense"
        :requiresLicense="$requiresLicense"
        modalTrigger="updateModalOpen"
    />

    {{-- License Activation Modal (for pro modules without license) --}}
    @if($requiresLicense && !$hasValidLicense)
        <x-modals.license-activation
            id="license-modal-{{ $moduleSlug }}"
            :moduleSlug="$moduleSlug"
            :moduleName="$module->title"
            modalTrigger="licenseModalOpen"
        />
    @endif
</div>

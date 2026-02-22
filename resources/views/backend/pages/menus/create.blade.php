<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <form
        action="{{ route('admin.menus.store') }}"
        method="POST"
        data-prevent-unsaved-changes
    >
        @csrf

        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                        <iconify-icon icon="lucide:menu" class="text-xl"></iconify-icon>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Menu Details') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-normal">{{ __('Configure the menu name and location') }}</p>
                    </div>
                </div>
            </x-slot>

            <x-slot name="headerRight">
                <x-buttons.submit-buttons
                    :submitLabel="__('Create Menu')"
                    cancelUrl="{{ route('admin.menus.index') }}"
                />
            </x-slot>

            <div class="grid gap-6 max-w-2xl">
                <x-inputs.input
                    name="name"
                    id="name"
                    :label="__('Menu Name')"
                    :value="old('name')"
                    :placeholder="__('e.g., Main Navigation')"
                    required
                    autofocus
                />

                @php
                    // Filter out already used locations
                    $availableLocations = collect($locations)->filter(function ($label, $value) use ($usedLocations) {
                        return !in_array($value, $usedLocations);
                    })->toArray();
                @endphp

                <x-inputs.select
                    name="location"
                    id="location"
                    :label="__('Menu Location')"
                    :options="$availableLocations"
                    :value="old('location')"
                    :placeholder="__('Select a location')"
                    :hint="__('The location determines where the menu will be displayed on the frontend.')"
                    required
                />

                @if(count($usedLocations) > 0)
                    <div class="text-sm text-gray-500 dark:text-gray-400 -mt-4">
                        <span class="font-medium">{{ __('Already assigned:') }}</span>
                        @foreach($usedLocations as $usedLocation)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 ml-1">
                                {{ $locations[$usedLocation] ?? $usedLocation }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <x-inputs.textarea
                    name="description"
                    id="description"
                    :label="__('Description')"
                    :value="old('description')"
                    :placeholder="__('Optional description for this menu')"
                    :rows="3"
                />

                <div>
                    <label class="form-label">{{ __('Status') }}</label>
                    <input type="hidden" name="status" value="inactive">
                    <x-inputs.toggle
                        name="status"
                        :label="__('Active')"
                        :checked="old('status', 'active') === 'active'"
                        value="active"
                        :hint="__('Inactive menus will not be displayed on the frontend.')"
                    />
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex justify-end">
                    <x-buttons.submit-buttons
                        :submitLabel="__('Create Menu')"
                        cancelUrl="{{ route('admin.menus.index') }}"
                    />
                </div>
            </x-slot>
        </x-card>
    </form>
</x-layouts.backend-layout>

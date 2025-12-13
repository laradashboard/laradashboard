{!! Hook::applyFilters(SettingFilterHook::SETTINGS_CONTENT_TAB_BEFORE_SECTION_START, '') !!}
<x-card>
    <x-slot name="header">
        {{ __("Content Settings") }}
    </x-slot>
    <div class="space-y-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
            <div class="flex-1">
                <x-inputs.combobox
                    name="default_pagination_ui"
                    :label="__('Default Pagination UI')"
                    :options="[
                        ['value' => 'default', 'label' => __('Default Pagination'), 'description' => __('Shows page numbers and navigation arrows.')],
                        ['value' => 'cursor', 'label' => __('Cursor Pagination'), 'description' => __('Efficient for large datasets, only next/previous.')],
                        ['value' => 'simple', 'label' => __('Simple Pagination'), 'description' => __('Shows only next/previous buttons.')]
                    ]"
                    :selected="config('settings.default_pagination_ui', 'default')"
                    searchable="false"
                    class="w-full"
                />
            </div>
            <div class="flex-1">
                <label class="form-label">
                    {{ __("Default Pagination per page") }}
                </label>
                <input
                    type="number"
                    name="default_pagination"
                    min="1"
                    value="{{ config('settings.default_pagination') ?? 10 }}"
                    class="form-control"
                />
            </div>
        </div>
    </div>
    {!! Hook::applyFilters(SettingFilterHook::SETTINGS_CONTENT_TAB_BEFORE_SECTION_END, '') !!}
</x-card>
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_CONTENT_TAB_AFTER_SECTION_END, '') !!}

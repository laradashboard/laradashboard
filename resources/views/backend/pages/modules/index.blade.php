<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(ModuleFilterHook::MODULES_AFTER_BREADCRUMBS, '') !!}

    <div x-data="{
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'installed',
        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
    }">
        {{-- Tab navigation --}}
        <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center"
                role="tablist"
                aria-label="{{ __('Module sections') }}">
                <li class="me-2" role="presentation">
                    <button
                        @click="setTab('installed')"
                        :class="activeTab === 'installed'
                            ? 'text-primary border-primary dark:text-primary dark:border-primary'
                            : 'text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'"
                        :aria-selected="activeTab === 'installed' ? 'true' : 'false'"
                        class="flex justify-center items-center p-4 border-b-2 rounded-t-lg"
                        type="button"
                        role="tab">
                        <iconify-icon icon="lucide:hard-drive" class="mr-2"></iconify-icon>
                        {{ __('Installed Modules') }}
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button
                        @click="setTab('marketplace')"
                        :class="activeTab === 'marketplace'
                            ? 'text-primary border-primary dark:text-primary dark:border-primary'
                            : 'text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'"
                        :aria-selected="activeTab === 'marketplace' ? 'true' : 'false'"
                        class="flex justify-center items-center p-4 border-b-2 rounded-t-lg"
                        type="button"
                        role="tab">
                        <iconify-icon icon="lucide:store" class="mr-2"></iconify-icon>
                        {{ __('Marketplace') }}
                    </button>
                </li>
            </ul>
        </div>

        {{-- Tab content --}}
        <div x-show="activeTab === 'installed'" role="tabpanel">
            {!! Hook::applyFilters(ModuleFilterHook::MODULES_BEFORE_LIST, '') !!}

            <livewire:datatable.module-datatable />

            {!! Hook::applyFilters(ModuleFilterHook::MODULES_AFTER_LIST, '') !!}
        </div>

        <div x-show="activeTab === 'marketplace'" x-cloak role="tabpanel">
            <livewire:marketplace.marketplace-module-browser />
        </div>
    </div>
</x-layouts.backend-layout>

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(SettingFilterHook::SETTINGS_AFTER_BREADCRUMBS, '') !!}

    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data"
            data-prevent-unsaved-changes>
            @csrf
            @include('backend.pages.settings.tabs', [
                'tabs' => Hook::applyFilters(SettingFilterHook::SETTINGS_TABS, [
                    'general' => [
                        'title' => __('General Settings'),
                        'icon' => 'lucide:settings',
                        'view' => 'backend.pages.settings.general-tab',
                    ],
                    'appearance' => [
                        'title' => __('Site Appearance'),
                        'icon' => 'lucide:palette',
                        'view' => 'backend.pages.settings.appearance-tab',
                    ],
                    'content' => [
                        'title' => __('Content Settings'),
                        'icon' => 'lucide:file-text',
                        'view' => 'backend.pages.settings.content-settings',
                    ],
                    'integrations' => [
                        'title' => __('Integrations'),
                        'icon' => 'lucide:plug',
                        'view' => 'backend.pages.settings.integration-settings',
                    ],
                    'performance-security' => [
                        'title' => __('Security'),
                        'icon' => 'lucide:shield',
                        'view' => 'backend.pages.settings.performance-security-tab',
                    ],
                ]),
            ])

            <div class="mt-4">
                <x-buttons.submit-buttons :submit-label="__('Save Changes')" />
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabButtons = document.querySelectorAll('[role="tab"]');
                const controllerTab = "{{ $tab }}";

                function setActiveTab(tabKey) {
                    tabButtons.forEach(button => {
                        const isActive = button.getAttribute('data-tab') === tabKey;

                        button.classList.toggle('text-primary', isActive);
                        button.classList.toggle('border-primary', isActive);
                        button.classList.toggle('dark:text-primary', isActive);
                        button.classList.toggle('dark:border-primary', isActive);
                        button.classList.toggle('text-gray-500', !isActive);
                        button.classList.toggle('border-transparent', !isActive);
                    });

                    // Optional: Show/hide corresponding tab content
                    document.querySelectorAll('[role="tabpanel"]').forEach(panel => {
                        panel.style.display = panel.id === tabKey ? 'block' : 'none';
                    });
                }

                // Handle click
                tabButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const tabKey = this.getAttribute('data-tab');
                        const url = new URL(window.location);
                        url.searchParams.set('tab', tabKey);
                        window.history.pushState({}, '', url);

                        setActiveTab(tabKey);
                    });
                });

                // On page load, set active tab from URL or controller
                const urlTab = new URL(window.location).searchParams.get('tab');
                const activeTab = urlTab || controllerTab || 'general';
                setActiveTab(activeTab);
            });
        </script>
    @endpush
</x-layouts.backend-layout>

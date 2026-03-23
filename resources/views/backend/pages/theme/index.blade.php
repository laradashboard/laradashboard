<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.theme.store') }}" enctype="multipart/form-data"
            data-prevent-unsaved-changes>
            @csrf
            @include('backend.pages.settings.tabs', [
                'tabs' => [
                    'choose-theme' => [
                        'title' => __('Choose Theme'),
                        'icon' => 'lucide:layout-template',
                        'view' => 'backend.pages.theme.choose-theme-tab',
                        'data' => ['themes' => $themes, 'activeTheme' => $activeTheme],
                    ],
                    'appearance' => [
                        'title' => __('Appearance'),
                        'icon' => 'lucide:palette',
                        'view' => 'backend.pages.theme.appearance-tab',
                    ],
                    'admin-theme' => [
                        'title' => __('Admin Theme'),
                        'icon' => 'lucide:monitor',
                        'view' => 'backend.pages.theme.admin-theme-tab',
                        'data' => ['colorPresets' => $colorPresets],
                    ],
                    'custom-code' => [
                        'title' => __('Custom Code'),
                        'icon' => 'lucide:code',
                        'view' => 'backend.pages.theme.custom-code-tab',
                    ],
                ],
            ])

            <div class="mt-4" id="theme-save-btn">
                <x-buttons.submit-buttons :submit-label="__('Save Changes')" />
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabButtons = document.querySelectorAll('[role="tab"]');
                const controllerTab = "{{ $tab }}";
                const saveBtn = document.getElementById('theme-save-btn');

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

                    // Show/hide corresponding tab content
                    document.querySelectorAll('[role="tabpanel"]').forEach(panel => {
                        panel.style.display = panel.id === tabKey ? 'block' : 'none';
                    });

                    // Hide save button on choose-theme tab
                    if (saveBtn) {
                        saveBtn.style.display = tabKey === 'choose-theme' ? 'none' : 'block';
                    }
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
                const activeTab = urlTab || controllerTab || 'choose-theme';
                setActiveTab(activeTab);
            });
        </script>
    @endpush
</x-layouts.backend-layout>

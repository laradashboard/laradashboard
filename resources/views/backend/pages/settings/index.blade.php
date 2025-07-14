@extends('backend.layouts.app')

@section('title')
    {{ ucfirst($tab ?? '') . ' ' . __('Settings') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('settings_after_breadcrumbs', '') !!}

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <form method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('backend.pages.settings.tabs', [
                            'tabs' => ld_apply_filters('settings_tabs', [
                                'general' => [
                                    'title' => __('General Settings'),
                                    'view' => 'backend.pages.settings.general-tab',
                                ],
                                'appearance' => [
                                    'title' => __('Site Appearance'),
                                    'view' => 'backend.pages.settings.appearance-tab',
                                ],
                                'content' => [
                                    'title' => __('Content Settings'),
                                    'view' => 'backend.pages.settings.content-settings',
                                ],
                                'integrations' => [
                                    'title' => __('Integrations'),
                                    'view' => 'backend.pages.settings.integration-settings',
                                ],
                            ]),
                        ])

                        <x-buttons.submit-buttons  />
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('[role="tab"]');

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
            button.addEventListener('click', function () {
                const tabKey = this.getAttribute('data-tab');
                const url = new URL(window.location);
                url.searchParams.set('tab', tabKey);
                window.history.pushState({}, '', url);

                setActiveTab(tabKey);
            });
        });

        // On page load, set active tab from URL
        const urlTab = new URL(window.location).searchParams.get('tab') || 'general';
        setActiveTab(urlTab);
    });
</script>
@endpush

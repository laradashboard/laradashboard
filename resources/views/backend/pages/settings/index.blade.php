@extends('backend.layouts.app')

@section('title')
    {{ ucfirst($tab ?? '') . ' ' . __('Settings - ' . config('app.name')) }}
@endsection

@php
    $isActionLogExist = false;
@endphp
@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <div x-data="{ pageName: 'Settings' }">
            <!-- Page Header -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Settings</h2>

                <nav>
                    <ol class="flex items-center gap-1.5">
                        <li>
                            <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                href="{{ route('admin.dashboard') }}">
                                Home
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Settings</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Action Logs Table -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <form method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('backend.layouts.partials.messages')
                        @include('backend.pages.settings.tabs', [
                            'tabs' => [
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
                                ]
                            ],
                        ])

                        <!-- Submit Button -->
                        <div class="flex justify-start">
                            <button type="submit" class="btn-primary">
                                {{ __('Save') }}
                            </button>
                        </div>
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

                button.classList.toggle('text-blue-600', isActive);
                button.classList.toggle('border-blue-600', isActive);
                button.classList.toggle('dark:text-blue-500', isActive);
                button.classList.toggle('dark:border-blue-500', isActive);
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

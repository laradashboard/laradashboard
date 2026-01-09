@section('title', __('Dashboard') . ' | ' . config('app.name'))

@php
    $dashboardSections = Hook::applyFilters(DashboardFilterHook::DASHBOARD_SECTIONS, [
        'quick_actions',
        'stat_cards',
        'user_growth',
        'quick_draft',
        'post_chart',
        'recent_posts',
    ]);
@endphp

<x-layouts.backend-layout>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-white/90 flex items-center gap-2">
            {{ __('Hi :name', ['name' => auth()->user()->full_name]) }}
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Welcome back to the dashboard!') }}
        </p>
    </div>

    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_AFTER_BREADCRUMBS, '') !!}

    {{-- Quick Actions Panel --}}
    @if(in_array('quick_actions', $dashboardSections))
    <div class="mb-6">
        @include('backend.pages.dashboard.partials.quick-actions')
    </div>
    @endif

    {{-- Stat Cards --}}
    @if(in_array('stat_cards', $dashboardSections))
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 space-y-6">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-5 lg:grid-cols-5 md:gap-6">
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_BEFORE_USERS, '') !!}

                @can('post.view')
                @include('backend.pages.dashboard.partials.card', [
                    'icon' => 'heroicons:document-duplicate',
                    'icon_bg' => '#F59E0B',
                    'label' => __('Posts'),
                    'value' => $total_posts,
                    'class' => 'bg-white',
                    'url' => route('admin.posts.index', 'post'),
                    'enable_full_div_click' => true,
                ])
                @endcan

                @can('user.view')
                @include('backend.pages.dashboard.partials.card', [
                    "icon" => 'heroicons:user-group',
                    'icon_bg' => 'var(--color-brand-500)',
                    'label' => __('Users'),
                    'value' => $total_users,
                    'class' => 'bg-white',
                    'url' => route('admin.users.index'),
                    'enable_full_div_click' => true,
                ])
                @endcan
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_USERS, '') !!}

                @can('role.view')
                @include('backend.pages.dashboard.partials.card', [
                    'icon' => 'heroicons:key',
                    'icon_bg' => '#00D7FF',
                    'label' => __('Roles'),
                    'value' => $total_roles,
                    'class' => 'bg-white',
                    'url' => route('admin.roles.index'),
                    'enable_full_div_click' => true,
                ])
                @endcan
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_ROLES, '') !!}

                @can('role.view')
                @include('backend.pages.dashboard.partials.card', [
                    'icon' => 'bi:shield-check',
                    'icon_bg' => '#FF4D96',
                    'label' => __('Permissions'),
                    'value' => $total_permissions,
                    'class' => 'bg-white',
                    'url' => route('admin.permissions.index'),
                    'enable_full_div_click' => true,
                ])
                @endcan
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_PERMISSIONS, '') !!}

                @can('settings.view')
                @include('backend.pages.dashboard.partials.card', [
                    'icon' => 'heroicons:language',
                    'icon_bg' => '#22C55E',
                    'label' => __('Translations'),
                    'value' => $languages['total'] . ' / ' . $languages['active'],
                    'class' => 'bg-white',
                    'url' => route('admin.translations.index'),
                    'enable_full_div_click' => true,
                ])
                @endcan
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_TRANSLATIONS, '') !!}
            </div>
        </div>
    </div>
    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER, '') !!}
    @endif

    @section('before_vite_build')
        <script>
            var userGrowthData = @json($user_growth_data['data']);
            var userGrowthLabels = @json($user_growth_data['labels']);
        </script>
    @endsection

    {{-- Charts Row: User Growth + Quick Draft --}}
    @if(in_array('user_growth', $dashboardSections) || in_array('quick_draft', $dashboardSections))
    @can('user.view')
    <div class="mt-6">
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            {{-- User Growth Chart --}}
            @if(in_array('user_growth', $dashboardSections))
            <div class="col-span-12 lg:col-span-8">
                @include('backend.pages.dashboard.partials.user-growth')
            </div>
            @endif
            {{-- Quick Draft Form --}}
            @if(in_array('quick_draft', $dashboardSections))
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
                @can('post.create')
                <livewire:dashboard.quick-draft />
                @endcan
            </div>
            @endif
        </div>
    </div>
    @endcan
    @endif

    {{-- Bottom Row: Post Activity + Recent Posts --}}
    @if(in_array('post_chart', $dashboardSections) || in_array('recent_posts', $dashboardSections))
    <div class="mt-6">
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            {{-- Post Activity Chart --}}
            @if(in_array('post_chart', $dashboardSections))
            @can('post.view')
            <div class="col-span-12 lg:col-span-8">
                <div class="grid grid-cols-12 gap-4 md:gap-6">
                    @include('backend.pages.dashboard.partials.post-chart')
                </div>
            </div>
            @endcan
            @endif

            {{-- Recent Posts Sidebar --}}
            @if(in_array('recent_posts', $dashboardSections))
            <div class="col-span-12 lg:col-span-4">
                @can('post.view')
                <livewire:dashboard.recent-posts :limit="5" />
                @endcan
            </div>
            @endif
        </div>
    </div>
    @endif

    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_AFTER, '') !!}
</x-layouts.backend-layout>

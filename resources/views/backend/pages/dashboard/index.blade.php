@section('title', __('Dashboard') . ' | ' . config('app.name'))

@php
    use App\Services\Dashboard\DashboardWidgetService;

    $widgetService = app(DashboardWidgetService::class);
    $dashboardSections = Hook::applyFilters(DashboardFilterHook::DASHBOARD_SECTIONS, [
        'quick_actions',
        'stat_cards',
        'user_growth',
        'quick_draft',
        'post_chart',
        'recent_posts',
    ]);

    $showUserGrowth = in_array('user_growth', $dashboardSections)
        && $widgetService->isVisible('user_growth')
        && auth()->user()->can('user.view');

    $showQuickDraft = in_array('quick_draft', $dashboardSections)
        && $widgetService->isVisible('quick_draft')
        && auth()->user()->can('post.create');

    $showPostChart = in_array('post_chart', $dashboardSections)
        && $widgetService->isVisible('post_chart')
        && auth()->user()->can('post.view');

    $showRecentPosts = in_array('recent_posts', $dashboardSections)
        && $widgetService->isVisible('recent_posts')
        && auth()->user()->can('post.view');
@endphp

<x-layouts.backend-layout>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-700 dark:text-white/90 flex items-center gap-2">
                {{ __('Hi :name', ['name' => auth()->user()->full_name]) }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Welcome back to the dashboard!') }}
            </p>
        </div>

        <livewire:dashboard.widget-customizer />
    </div>

    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_AFTER_BREADCRUMBS, '') !!}

    {{-- Quick Actions Panel --}}
    @if(in_array('quick_actions', $dashboardSections) && $widgetService->isVisible('quick_actions'))
        <div class="mb-6">
            @include('backend.pages.dashboard.partials.quick-actions')
        </div>
    @endif

    {{-- Stat Cards --}}
    @if(in_array('stat_cards', $dashboardSections))
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 space-y-6">
                <livewire:dashboard.stat-card-grid :context="[
                    'total_posts' => $total_posts,
                    'total_users' => $total_users,
                    'total_roles' => $total_roles,
                    'total_permissions' => $total_permissions,
                    'languages' => $languages,
                ]" />
            </div>
        </div>
        {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER, '') !!}
    @endif

    {{-- Charts Row: User Growth + Quick Draft --}}
    @if($showUserGrowth || $showQuickDraft)
        <div class="mt-6">
            <div class="grid grid-cols-12 gap-4 md:gap-6">
                @if($showUserGrowth)
                    <div @class([
                        'col-span-12',
                        'lg:col-span-8' => $showQuickDraft,
                        'lg:col-span-12' => ! $showQuickDraft,
                    ])>
                        @include('backend.pages.dashboard.partials.user-growth', [
                            'user_growth_data' => $user_growth_data,
                        ])
                    </div>
                @endif

                @if($showQuickDraft)
                    <div @class([
                        'col-span-12',
                        'md:col-span-6 lg:col-span-4' => $showUserGrowth,
                        'lg:col-span-12' => ! $showUserGrowth,
                    ])>
                        <livewire:dashboard.quick-draft />
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Bottom Row: Post Activity + Recent Posts --}}
    @if($showPostChart || $showRecentPosts)
        <div class="mt-6">
            <div class="grid grid-cols-12 gap-4 md:gap-6">
                @if($showPostChart)
                    <div @class([
                        'col-span-12',
                        'lg:col-span-8' => $showRecentPosts,
                        'lg:col-span-12' => ! $showRecentPosts,
                    ])>
                        <div class="grid grid-cols-12 gap-4 md:gap-6">
                            @include('backend.pages.dashboard.partials.post-chart')
                        </div>
                    </div>
                @endif

                @if($showRecentPosts)
                    <div @class([
                        'col-span-12',
                        'lg:col-span-4' => $showPostChart,
                        'lg:col-span-12' => ! $showPostChart,
                    ])>
                        <livewire:dashboard.recent-posts :limit="5" />
                    </div>
                @endif
            </div>
        </div>
    @endif

    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_AFTER, '') !!}
</x-layouts.backend-layout>

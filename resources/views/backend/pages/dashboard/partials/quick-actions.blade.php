@php
    $quickActions = [
        [
            'permission' => 'post.create',
            'route' => route('admin.posts.create', 'post'),
            'icon' => 'heroicons:document-plus',
            'label' => __('New Post'),
            'color' => 'var(--color-brand-500)',
        ],
        [
            'permission' => 'post.create',
            'route' => route('admin.posts.create', 'page'),
            'icon' => 'heroicons:document-text',
            'label' => __('New Page'),
            'color' => '#8B5CF6',
        ],
        [
            'permission' => 'user.create',
            'route' => route('admin.users.create'),
            'icon' => 'heroicons:user-plus',
            'label' => __('New User'),
            'color' => '#00D7FF',
        ],
        [
            'permission' => 'role.create',
            'route' => route('admin.roles.create'),
            'icon' => 'heroicons:shield-check',
            'label' => __('New Role'),
            'color' => '#FF4D96',
        ],
        [
            'permission' => 'settings.view',
            'route' => route('admin.settings.index'),
            'icon' => 'heroicons:cog-6-tooth',
            'label' => __('Settings'),
            'color' => '#22C55E',
        ],
        [
            'permission' => 'email_template.view',
            'route' => route('admin.email-templates.index'),
            'icon' => 'heroicons:envelope',
            'label' => __('Email Templates'),
            'color' => '#F59E0B',
        ],
        [
            'permission' => 'media.create',
            'route' => route('admin.media.index', ['new' => true]),
            'icon' => 'heroicons:photo',
            'label' => __('New Media'),
            'color' => '#EC4899',
        ],
    ];

    // Filter quick actions via hooks
    $quickActions = Hook::applyFilters(DashboardFilterHook::DASHBOARD_QUICK_ACTIONS, $quickActions);

    // Filter to only show actions user has permission for
    $visibleActions = collect($quickActions)->filter(function ($action) {
        return auth()->user()->can($action['permission']);
    });
@endphp

@if($visibleActions->isNotEmpty())
<x-dashboard-collapsible-card
    :title="__('Quick Actions')"
    icon="heroicons:bolt"
    icon-bg="bg-brand-100 dark:bg-brand-900/30"
    icon-color="text-brand-600 dark:text-brand-400"
    storage-key="dashboard_quick_actions"
    :collapsed-by-default="true"
>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-8">
        {{-- AI Agent Button --}}
        <button type="button"
                @click="$dispatch('open-ai-modal')"
                class="group flex flex-col items-center gap-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4 transition-all hover:border-gray-200 dark:hover:border-gray-600 hover:bg-white dark:hover:bg-gray-700 hover:shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg transition-transform group-hover:scale-110"
                 style="background-color: rgba(147, 51, 234, 0.1);">
                <iconify-icon icon="lucide:sparkles"
                              class="text-xl"
                              style="color: #9333EA;"></iconify-icon>
            </div>
            <span class="text-xs font-medium text-gray-600 dark:text-gray-300 text-center">{{ __('AI Agent') }}</span>
        </button>

        {{-- Regular Actions --}}
        @foreach($visibleActions as $action)
        <a href="{{ $action['route'] }}"
           class="group flex flex-col items-center gap-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4 transition-all hover:border-gray-200 dark:hover:border-gray-600 hover:bg-white dark:hover:bg-gray-700 hover:shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg transition-transform group-hover:scale-110"
                 style="background-color: {{ $action['color'] }}20;">
                <iconify-icon icon="{{ $action['icon'] }}"
                              class="text-xl"
                              style="color: {{ $action['color'] }};"></iconify-icon>
            </div>
            <span class="text-xs font-medium text-gray-600 dark:text-gray-300 text-center">{{ $action['label'] }}</span>
        </a>
        @endforeach
    </div>
</x-dashboard-collapsible-card>
@endif

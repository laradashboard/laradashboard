@extends('backend.layouts.app')

@section('title')
   {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('before_vite_build')
    <script>
        var userGrowthData = @json($user_growth_data['data']);
        var userGrowthLabels = @json($user_growth_data['labels']);
    </script>
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('dashboard_after_breadcrumbs', '') !!}

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 space-y-6">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-4 md:gap-6">
                    {!! ld_apply_filters('dashboard_cards_before_users', '') !!}
                    @include('backend.pages.dashboard.partials.card', [
                        'icon_svg' => asset('images/icons/user.svg'),
                        'label' => __('Users'),
                        'value' => $total_users,
                        'bg' => '#635BFF',
                        'class' => 'bg-white',
                        'url' => route('admin.users.index'),
                        'enable_full_div_click' => true,
                    ])
                    {!! ld_apply_filters('dashboard_cards_after_users', '') !!}
                    @include('backend.pages.dashboard.partials.card', [
                        'icon_svg' => asset('images/icons/key.svg'),
                        'label' => __('Roles'),
                        'value' => $total_roles,
                        'bg' => '#00D7FF',
                        'class' => 'bg-white',
                        'url' => route('admin.roles.index'),
                        'enable_full_div_click' => true,
                    ])
                    {!! ld_apply_filters('dashboard_cards_after_roles', '') !!}
                    @include('backend.pages.dashboard.partials.card', [
                        'icon' => 'bi bi-shield-check',
                        'label' => __('Permissions'),
                        'value' => $total_permissions,
                        'bg' => '#FF4D96',
                        'class' => 'bg-white',
                        'url' => route('admin.permissions.index'),
                        'enable_full_div_click' => true,
                    ])
                    {!! ld_apply_filters('dashboard_cards_after_permissions', '') !!}
                    @include('backend.pages.dashboard.partials.card', [
                        'icon' => 'bi bi-translate',
                        'label' => __('Translations'),
                        'value' => $languages['total'] . ' / ' . $languages['active'],
                        'bg' => '#22C55E',
                        'class' => 'bg-white',
                        'url' => route('admin.translations.index'),
                        'enable_full_div_click' => true,
                    ])
                    {!! ld_apply_filters('dashboard_cards_after_translations', '') !!}
                </div>
            </div>
        </div>

        {!! ld_apply_filters('dashboard_cards_after', '') !!}

        <div class="mt-6">
            <div class="grid grid-cols-12 gap-4 md:gap-6">
                <div class="col-span-12">
                    <div class="grid grid-cols-12 gap-4 md:gap-6">
                        <div class="col-span-12 md:col-span-8">
                            @include('backend.pages.dashboard.partials.user-growth')
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            @include('backend.pages.dashboard.partials.user-history')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {!! ld_apply_filters('dashboard_after', '') !!}
    </div>
@endsection

@extends('backend.layouts.app')

@section('title')
    {{ __('Dashboard Page') }} - {{ config('app.name') }}
@endsection

@section('before_vite_build')
    <script>
        var userGrowthData = @json($user_growth_data['data']);
        var userGrowthLabels = @json($user_growth_data['labels']);
    </script>
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:gap-6">
                    @include('backend.pages.dashboard.card', [
                        'icon' => asset('images/icons/user.svg'),
                        'test' => __('Users'),
                        'number' => $total_users,
                        'bg' => '#635BFF',
                        'class' => 'bg-white col-span-4',
                    ])
                    @include('backend.pages.dashboard.card', [
                        'icon' => asset('images/icons/user.svg'),
                        'test' => __('Roles'),
                        'number' => $total_roles,
                        'bg' => '#00D7FF',
                        'class' => 'bg-white col-span-4',
                    ])
                    @include('backend.pages.dashboard.card', [
                        'icon' => asset('images/icons/user.svg'),
                        'test' => __('Permissions'),
                        'number' => $total_permissions,
                        'bg' => '#FF4D96',
                        'class' => 'bg-white col-span-4',
                    ])
                </div>
            </div>
        </div>

        <div class="mt-6">
            <div class="grid grid-cols-12 gap-4 md:gap-6">
                <div class="col-span-12">
                    <div class="grid grid-cols-12 gap-4 md:gap-6">
                        <div class="col-span-12 md:col-span-8">
                            @include('backend.pages.dashboard.user-groawth')
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            @include('backend.pages.dashboard.user-pie-chart')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@props([
    'breadcrumbs' => [],
    // Optional first-paint sticky title. Datatables apply unified page scroll on their own.
    'unifiedScroll' => false,
])

@extends('backend.layouts.app')

@section('title')
    @if ($pageTitle ?? false)
        {{ $pageTitle }}
    @else
        {{ $breadcrumbs['title'] ?? '' }} | {{ config('app.name') }}
    @endif
@endsection

@section('admin-content')
    <div class="ld-container">
        @if ($unifiedScroll)
            <x-datatable.unified-scroll-header>
                @if ($breadcrumbsData ?? false)
                    {!! $breadcrumbsData !!}
                @else
                    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />
                @endif
            </x-datatable.unified-scroll-header>
        @elseif ($breadcrumbsData ?? false)
            {!! $breadcrumbsData !!}
        @else
            <x-breadcrumbs :breadcrumbs="$breadcrumbs" />
        @endif

        {{ $slot }}
    </div>
@endsection

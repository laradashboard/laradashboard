@props(['breadcrumbs' => []])

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
        @if ($breadcrumbsData ?? false)
            {!! $breadcrumbsData !!}
        @else
            <x-breadcrumbs :breadcrumbs="$breadcrumbs" />
        @endif

        {{ $slot }}
    </div>
@endsection

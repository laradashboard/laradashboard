@extends('backend.layouts.app')

@section('title')
    @yield('squartup-title', $breadcrumbs['title'] ?? __('Squartup')) | {{ __('Squartup') }} | {{ config('app.name') }}
@endsection

@push('styles')
    {{-- Resilient CSS loader: renders the compiled module bundle when present and
         degrades gracefully (no 500) when it has not been built yet. --}}
    <x-module-styles :entrypoints="['modules/Squartup/resources/assets/css/app.css']" build="build-squartup" />
@endpush

@section('admin-content')
    <div class="squartup-module container px-6 py-8 mx-auto min-h-[80vh]">
        @yield('squartup-admin-content')
    </div>
@endsection

@push('scripts')
    @stack('squartup-scripts')
@endpush

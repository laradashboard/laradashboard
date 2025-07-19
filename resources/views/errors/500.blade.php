@extends('backend.auth.layouts.app')

@section('title')
    500 - {{ __('Internal Server Error') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="relative z-1 flex min-h-screen flex-col items-center justify-center overflow-hidden p-6">
    <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
        <h1 class="mb-8 text-title-md font-bold text-gray-700 dark:text-white/90 xl:text-title-2xl">
            {{ __('ERROR') }}
        </h1>
        <h1 class="mb-8 text-title-md font-bold text-gray-700 dark:text-white/90 xl:text-title-2xl">
            500
        </h1>

        <p class="mt-2">
            {{ __('Internal Server Error!') }}
        </p>

        <p class="mb-6 mt-10 text-base text-gray-700 dark:text-gray-300 sm:text-lg">
            {{ __('Something went wrong on our end. Please try again later.') }}
        </p>

        @include('errors.partials.links')
    </div>
</div>
@endsection

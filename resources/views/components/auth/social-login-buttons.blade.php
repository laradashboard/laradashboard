@props([
    'dividerText' => null,
])

@inject('socialAuthService', 'App\Services\SocialAuthService')

@php
    $providers = $socialAuthService->getEnabledProviders();
@endphp

@if(count($providers) > 0)
    {{-- Divider --}}
    <div class="relative my-5">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-3 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                {{ $dividerText ?? __('Or continue with') }}
            </span>
        </div>
    </div>

    {{-- Social Login Buttons --}}
    <div class="grid gap-3 {{ count($providers) > 2 ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-' . count($providers) }}">
        @foreach($providers as $provider => $config)
            <a
                href="{{ route('social.redirect', $provider) }}"
                class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 dark:focus:ring-offset-gray-900 transition-colors"
                title="{{ __('Continue with :provider', ['provider' => $config['name']]) }}"
            >
                <iconify-icon
                    icon="{{ $config['icon'] }}"
                    class="text-lg"
                    @if($provider === 'github') style="color: currentColor;" @endif
                ></iconify-icon>
                <span class="hidden sm:inline">{{ $config['name'] }}</span>
            </a>
        @endforeach
    </div>
@endif

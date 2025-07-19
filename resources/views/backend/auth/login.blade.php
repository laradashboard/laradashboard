@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Sign In') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
      <h1 class="mb-2 font-semibold text-gray-700 text-title-sm dark:text-white/90 sm:text-title-md">
        {{ __('Sign In') }}
      </h1>
      <p class="text-sm text-gray-500 dark:text-gray-300">
        {{ __('Enter your email and password to sign in!') }}
      </p>
    </div>
    <div>
      <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf
        <div class="space-y-5">
          <x-messages />

          <div>
            <label class="form-label">{{ __('Email') }}</label>
            <input autofocus type="text" id="email" name="email" autocomplete="username" placeholder="{{ __('Enter your email') }}" class="dark:bg-dark-900 h-11 w-full rounded-md border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-700 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" value="{{ old('email') ?? config('app.demo_mode', false) ? 'superadmin@example.com' : '' }}" required>
          </div>

          <x-inputs.password 
            name="password" 
            label="{{ __('Password') }}"
            placeholder="{{ __('Enter your password') }}" 
            value="{{ (config('app.demo_mode', false) ? '12345678' : '') }}" 
            required
          />

          <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center justify-center gap-2 text-sm font-medium has-checked:text-gray-900 dark:has-checked:text-white has-disabled:cursor-not-allowed">
                <span class="relative flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="before:content[''] peer relative size-4 appearance-none overflow-hidden rounded-sm border border-outline bg-surface-alt before:absolute before:inset-0 checked:border-primary checked:before:bg-primary focus:outline-2 focus:outline-offset-2 focus:outline-outline-strong checked:focus:outline-primary active:outline-offset-0 disabled:cursor-not-allowed dark:border-outline-dark dark:bg-surface-dark-alt dark:checked:border-primary-dark dark:checked:before:bg-primary-dark dark:focus:outline-outline-dark-strong dark:checked:focus:outline-primary-dark" checked/>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="4" class="pointer-events-none invisible absolute left-1/2 top-1/2 size-3 -translate-x-1/2 -translate-y-1/2 text-white peer-checked:visible">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </span>
                <span class="form-label mb-0">{{ __('Remember me') }}</span>
            </label>
            <a href="{{ route('admin.password.request') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('Forgot password?') }}</a>
          </div>

          <div>
            <button type="submit" class="btn-primary w-full ">
              {{ __('Sign In') }}
              <iconify-icon icon="lucide:log-in" class="ml-2"></iconify-icon>
            </button>
          </div>
          @if (config('app.demo_mode', false))
          <div x-data="{ showDemoCredentials: false }" class="mt-4 border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
            <button
              type="button"
              @click="showDemoCredentials = !showDemoCredentials"
              class="flex justify-between items-center w-full px-4 py-3 text-sm font-medium text-left text-brand-600 dark:text-brand-400 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              <span>
                <iconify-icon icon="lucide:info" class="mr-2"></iconify-icon>
                {{ __('Demo Credentials') }}
              </span>
              <iconify-icon :icon="showDemoCredentials ? 'lucide:chevron-up' : 'lucide:chevron-down'"></iconify-icon>
            </button>

            <div x-show="showDemoCredentials" x-transition class="px-4 py-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
              <div class="mb-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Use these credentials to explore the demo:') }}</p>
              </div>
              <div class="grid grid-cols-1 gap-2 mb-3 text-gray-500 dark:text-gray-300">
                <div class="flex items-center">
                  <span class="w-20 text-xs font-medium">{{ __('Email:') }}</span>
                  <code class="px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-800 rounded">superadmin@example.com</code>
                </div>
                <div class="flex items-center">
                  <span class="w-20 text-xs font-medium">{{ __('Password:') }}</span>
                  <code class="px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-800 rounded">12345678</code>
                </div>
              </div>
              <button
                type="button"
                id="fill-demo-credentials"
                class="!text-xs btn-default"
              >
                {{ __('Login Now') }}
                <iconify-icon icon="lucide:log-in" class="ml-1"></iconify-icon>
              </button>
            </div>
          </div>
          @endif
        </div>
      </form>
    </div>
</div>
@endsection

@if (config('app.demo_mode', false))
    @push('scripts')
        <script>
            document.getElementById('fill-demo-credentials').addEventListener('click', function() {
              console.log('clicked');
                document.getElementById('email').value = 'superadmin@example.com';
                document.querySelector('input[name="password"]').value = '12345678';
                document.querySelector('form').submit();
            });
        </script>
    @endpush
@endif

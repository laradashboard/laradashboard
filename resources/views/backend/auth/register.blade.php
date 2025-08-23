@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Register') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
      <h1 class="mb-2 font-semibold text-gray-700 text-title-sm dark:text-white/90 sm:text-title-md">
        {{ __('Register') }}
      </h1>
      <p class="text-sm text-gray-500 dark:text-gray-300">
        {{ __('Enter your phone number and password to create an account!') }}
      </p>
    </div>
    <div>
      <form action="{{ route('admin.register.submit') }}" method="POST">
        @csrf
        <div class="space-y-5">
          <x-messages />

          <div>
            <label class="form-label">{{ __('Name') }}</label>
            <input type="text" name="name" placeholder="{{ __('Enter your name') }}" value="{{ old('name') }}" required
              class="form-input" />
          </div>

          <div>
            <label class="form-label">{{ __('Phone Number') }}</label>
            <input type="text" name="phone" placeholder="{{ __('Enter your phone') }}" value="{{ old('phone') }}" required
              class="form-input" />
          </div>

          <div>
            <label class="form-label">{{ __('Password') }}</label>
            <input type="password" name="password" placeholder="{{ __('Enter your password') }}" required
              class="form-input" />
          </div>

          <div>
            <label class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" placeholder="{{ __('Confirm password') }}" required
              class="form-input" />
          </div>

          <div>
            <button type="submit" class="btn-primary w-full">
              {{ __('Register') }}
            </button>
          </div>
        </div>
      </form>
    </div>
</div>
@endsection

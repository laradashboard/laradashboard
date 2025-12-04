<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Confirm Unsubscribe') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 mb-4">
                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Confirm Unsubscribe') }}</h1>
            
            <p class="text-gray-600 dark:text-gray-300 mb-6">
                {{ __('Are you sure you want to unsubscribe from our email list?') }}
            </p>
            
            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-4 mb-6">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ __('Email') }}: <span class="font-medium">{{ $email }}</span>
                </p>
            </div>
            
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4 mb-6">
                <p class="text-sm text-blue-800 dark:text-blue-300">
                    {{ __("You'll no longer receive promotional emails, newsletters, and marketing updates. Important account notifications will still be sent.") }}
                </p>
            </div>
            
            <div class="space-y-3">
                <form method="POST" action="{{ route('unsubscribe.confirmed', $encryptedEmail) }}">
                    @csrf
                    <button type="submit" 
                            class="w-full inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        {{ __('Yes, Unsubscribe Me') }}
                    </button>
                </form>
                
                <a href="{{ url('/') }}" 
                   class="w-full inline-flex justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Cancel') }}
                </a>
            </div>
            
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-6">
                {{ __('Changed your mind? You can always resubscribe by contacting our support team.') }}
            </p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Unsubscribed Successfully' : 'Unsubscribe Error' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
            @if($success)
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-4">Successfully Unsubscribed</h1>
                <p class="text-gray-600 mb-6">{{ $message }}</p>
                @if($email)
                    <p class="text-sm text-gray-500 mb-6">
                        Email: <span class="font-medium">{{ $email }}</span>
                    </p>
                @endif
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <p class="text-sm text-green-800">
                        You will no longer receive promotional emails from us. 
                        You may still receive important account-related notifications.
                    </p>
                </div>
            @else
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-4">Unsubscribe Error</h1>
                <p class="text-gray-600 mb-6">{{ $message }}</p>
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <p class="text-sm text-red-800">
                        If you continue to have issues, please contact our support team.
                    </p>
                </div>
            @endif
            
            <div class="space-y-3">
                <a href="{{ url('/') }}" 
                   class="w-full inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Return to Homepage
                </a>
                
                @if($success && $email)
                    <p class="text-xs text-gray-500">
                        Changed your mind? 
                        <a href="mailto:support@{{ request()->getHost() }}?subject=Resubscribe Request&body=Please resubscribe {{ $email }}" 
                           class="text-blue-600 hover:text-blue-500">
                            Contact us to resubscribe
                        </a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
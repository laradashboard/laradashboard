<a href="{{ url()->previous() }}" class="inline-flex items-center justify-center mb-1 rounded-md border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
    <iconify-icon icon="lucide:arrow-left" class="mr-2"></iconify-icon>
    {{ __('Back') }}
</a>

<a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center mb-1 rounded-md border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
    <iconify-icon icon="lucide:grid" class="mr-2"></iconify-icon>
    {{ __('Back to Dashboard') }}
</a>

<form method="POST" action="{{ route('logout') }}" class="inline">
    @csrf
    <button type="submit" class="inline-flex items-center justify-center mb-1 rounded-md border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
        {{ __('Login Again') }}
        <iconify-icon icon="lucide:arrow-right" class="ml-2"></iconify-icon>
    </button>
</form>

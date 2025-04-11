<form method="POST" action="{{ route('admin.settings.store') }}">
    @csrf
    @include('backend.layouts.partials.messages')
    <div class="rounded-2xl border border-gray-200  dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Content Settings
            </h3>
        </div>
        <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Site default pagination
                </label>
                <input type="number" name="default_pagination" value="{{ config('settings.default_pagination') ?? 20 }}"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Submit &nbsp;
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</form>
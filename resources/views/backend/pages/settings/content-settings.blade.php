<div
    class="rounded-2xl border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]"
>
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
            {{ __("Content Settings") }}
        </h3>
    </div>
    <div
        class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800"
    >
        <div class="flex">
            <div class="md:basis-1/2">
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    {{ __("Default Pagination") }}
                </label>
                <input
                    type="number"
                    name="default_pagination"
                    min="1"
                    value="{{ config('settings.default_pagination') ?? 10 }}"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                />
            </div>
        </div>
    </div>
</div>

<div class="flex items-center space-x-4">
    <!-- Badge for selected filter -->
    <span
        class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-gray-700 dark:text-gray-200"
    >
        {{ ucfirst(str_replace("_", " ", $currentFilter)) }}
    </span>

    <button id="dropdownDefaultButton" data-dropdown-toggle="dropdown" class="btn-primary flex items-center justify-center gap-2" type="button ">
        <i class="bi bi-sliders"></i>
        {{ __('Filter') }}
        <i class="bi bi-chevron-down"></i>
    </button>

    <!-- Dropdown menu -->
    <div
        id="dropdown"
        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700"
    >
        <ul
            class="py-2 text-sm text-gray-700 dark:text-gray-200"
            aria-labelledby="dropdownDefaultButton"
        >
            <li>
                <a
                    href="{{
                        route('admin.dashboard')
                    }}?chart_filter_period=last_12_months"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{
                        $currentFilter === 'last_12_months'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''
                    }}"
                >
                    <span class="ml-2">Last 12 Months</span>
                </a>
            </li>
            <li>
                <a
                    href="{{
                        route('admin.dashboard')
                    }}?chart_filter_period=this_year"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{
                        $currentFilter === 'this_year'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''
                    }}"
                >
                    <span class="ml-2">This Year</span>
                </a>
            </li>
            <li>
                <a
                    href="{{
                        route('admin.dashboard')
                    }}?chart_filter_period=last_year"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{
                        $currentFilter === 'last_year'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''
                    }}"
                >
                    <span class="ml-2">Last Year</span>
                </a>
            </li>
            <li>
                <a
                    href="{{
                        route('admin.dashboard')
                    }}?chart_filter_period=last_30_days"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{
                        $currentFilter === 'last_30_days'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''
                    }}"
                >
                    <span class="ml-2">Last 30 Days</span>
                </a>
            </li>
            <li>
                <a
                    href="{{
                        route('admin.dashboard')
                    }}?chart_filter_period=last_7_days"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{
                        $currentFilter === 'last_7_days'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''
                    }}"
                >
                    <span class="ml-2">Last 7 Days</span>
                </a>
            </li>
            <li>
                <a
                    href="{{
                        route('admin.dashboard')
                    }}?chart_filter_period=this_month"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{
                        $currentFilter === 'this_month'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''
                    }}"
                >
                    <span class="ml-2">This Month</span>
                </a>
            </li>
        </ul>
    </div>
</div>

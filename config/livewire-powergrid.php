<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    |
    | PowerGrid supports Tailwind and Bootstrap 5 themes.
    | Configure the theme used in your application.
    |
    */
    'theme' => 'tailwind',

    /*
    |--------------------------------------------------------------------------
    | Plugins
    |--------------------------------------------------------------------------
    |
    | Plugins used by PowerGrid.
    |
    */
    'plugins' => [
        'flatpickr' => [
            'locales' => [
                'pt_BR' => [
                    'locale'     => 'pt',
                    'dateFormat' => 'd/m/Y H:i',
                    'enableTime' => true,
                    'time_24hr'  => true,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cached Views
    |--------------------------------------------------------------------------
    |
    | If enabled, PowerGrid will cache the component's view.
    |
    */
    'cached_views' => false,

    /*
    |--------------------------------------------------------------------------
    | Filter
    |--------------------------------------------------------------------------
    |
    | PowerGrid filter configuration.
    |
    */
    'filter' => [
        'date_picker' => [
            'enabled' => true,
            'format'  => 'Y-m-d',
        ],
        'multi_select' => [
            'enabled' => true,
            'max_options' => 10,
        ],
        'select' => [
            'enabled' => true,
        ],
        'boolean' => [
            'enabled' => true,
        ],
        'input_text' => [
            'enabled' => true,
        ],
        'number' => [
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exportable
    |--------------------------------------------------------------------------
    |
    | PowerGrid export configuration.
    |
    */
    'exportable' => [
        'default' => 'openspout',
        'drivers' => [
            'openspout' => [
                'xlsx' => \PowerComponents\LivewirePowerGrid\Components\Exports\OpenSpout\ExportToXLSX::class,
                'csv'  => \PowerComponents\LivewirePowerGrid\Components\Exports\OpenSpout\ExportToCSV::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Livewire configuration for PowerGrid.
    |
    */
    'livewire' => [
        'lazy' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Responsive table
    |--------------------------------------------------------------------------
    |
    | Default responsive table breakpoint.
    |
    */
    'responsive_table' => [
        'enabled' => true,
        'breakpoint' => 'md',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable PowerGrid features.
    |
    */
    'features' => [
        'bulk_actions' => true,
        'checkbox' => true,
        'radio' => true,
        'click_to_edit' => true,
        'lazy_loading' => true,
        'search' => true,
        'filters' => true,
        'pagination' => true,
        'per_page' => true,
        'exportable' => true,
        'show_columns' => true,
        'responsive' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of records per page.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'per_page_values' => [10, 15, 25, 50, 100],
    ],

    /*
    |--------------------------------------------------------------------------
    | Row Actions
    |--------------------------------------------------------------------------
    |
    | Row actions configuration.
    |
    */
    'row_actions' => [
        'enabled' => true,
        'view' => 'livewire-powergrid::components.actions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkbox Actions
    |--------------------------------------------------------------------------
    |
    | Checkbox actions configuration.
    |
    */
    'checkbox_actions' => [
        'enabled' => true,
        'view' => 'livewire-powergrid::components.checkbox-actions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Rules
    |--------------------------------------------------------------------------
    |
    | Action rules configuration.
    |
    */
    'action_rules' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Listeners
    |--------------------------------------------------------------------------
    |
    | PowerGrid listeners configuration.
    |
    */
    'listeners' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi Sort
    |--------------------------------------------------------------------------
    |
    | Enable multi-column sorting.
    |
    */
    'multi_sort' => [
        'enabled' => false,
        'max_columns' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Icons
    |--------------------------------------------------------------------------
    |
    | Icon configuration for PowerGrid components.
    |
    */
    'icons' => [
        'search' => 'heroicon-o-magnifying-glass',
        'filter' => 'heroicon-o-funnel',
        'export' => 'heroicon-o-arrow-down-tray',
        'columns' => 'heroicon-o-view-columns',
        'refresh' => 'heroicon-o-arrow-path',
        'edit' => 'heroicon-o-pencil',
        'delete' => 'heroicon-o-trash',
        'view' => 'heroicon-o-eye',
        'add' => 'heroicon-o-plus',
        'sort_asc' => 'heroicon-o-chevron-up',
        'sort_desc' => 'heroicon-o-chevron-down',
        'pagination_first' => 'heroicon-o-chevron-double-left',
        'pagination_previous' => 'heroicon-o-chevron-left',
        'pagination_next' => 'heroicon-o-chevron-right',
        'pagination_last' => 'heroicon-o-chevron-double-right',
    ],

    /*
    |--------------------------------------------------------------------------
    | CSS Classes
    |--------------------------------------------------------------------------
    |
    | CSS classes used by PowerGrid components.
    |
    */
    'css_classes' => [
        'table' => 'min-w-full divide-y divide-gray-200 dark:divide-gray-700',
        'thead' => 'bg-gray-50 dark:bg-gray-800',
        'tbody' => 'bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700',
        'tr' => 'hover:bg-gray-50 dark:hover:bg-gray-800',
        'th' => 'px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
        'td' => 'px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100',
        'button' => [
            'primary' => 'inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150',
            'secondary' => 'inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150',
            'danger' => 'inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150',
        ],
        'input' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm',
        'select' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm',
        'checkbox' => 'h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600',
    ],
];
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LaraDashboard Marketplace Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to the LaraDashboard marketplace for
    | module updates, licenses, and other marketplace-related features.
    |
    */

    'marketplace' => [
        /*
         * The base URL of the LaraDashboard marketplace.
         */
        'url' => env('MARKETPLACE_URL', 'https://laradashboard.com'),

        /*
         * The API endpoint for checking module updates.
         */
        'update_check_endpoint' => '/api/modules/check-updates',

        /*
         * The API endpoint for downloading module updates.
         */
        'download_endpoint' => '/api/modules/download',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Update Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for automatic module update checking.
    |
    */

    'updates' => [
        /*
         * Enable or disable automatic update checking.
         */
        'enabled' => env('MODULE_UPDATE_CHECK_ENABLED', true),

        /*
         * Cache duration in hours for update check results.
         * Updates will be cached to avoid hitting the API on every request.
         */
        'cache_duration' => 12,

        /*
         * The cache key for storing update check results.
         */
        'cache_key' => 'laradashboard:module_updates',

        /*
         * The cache key for storing the last update check timestamp.
         */
        'last_check_key' => 'laradashboard:last_update_check',

        /*
         * Minimum interval between fallback update checks (in minutes).
         * This prevents too many API calls when cron is not configured.
         */
        'fallback_throttle_minutes' => 60,
    ],
];

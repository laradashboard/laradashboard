<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upgrade Snapshot Storage
    |--------------------------------------------------------------------------
    |
    | Directory where pre/post upgrade snapshots are stored for verification.
    |
    */

    'snapshot_path' => storage_path('app/upgrade-snapshots'),

    /*
    |--------------------------------------------------------------------------
    | Post-Upgrade Verification
    |--------------------------------------------------------------------------
    */

    'verify' => [
        'http_routes' => [
            '/login',
            '/api/health',
        ],

        'http_timeout' => (int) env('RELEASE_VERIFY_HTTP_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Release Automation (GitHub Actions / internal API)
    |--------------------------------------------------------------------------
    |
    | Used when this installation acts as the marketplace publisher.
    | The LaraDashboard module reads RELEASE_API_TOKEN from env directly;
    | this config is for core-side documentation defaults only.
    |
    */

    'api_token' => env('RELEASE_API_TOKEN'),

    'github' => [
        'repository' => env('RELEASE_GITHUB_REPOSITORY', 'laradashboard/laradashboard'),
    ],

];

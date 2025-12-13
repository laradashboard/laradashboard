<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Email Subscription Feature
    |--------------------------------------------------------------------------
    |
    | This option controls whether the email subscription feature is enabled.
    |
    */
    'enabled' => env('EMAIL_SUBSCRIPTION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Unsubscribe Reasons
    |--------------------------------------------------------------------------
    |
    | Available reasons for users to select when unsubscribing.
    |
    */
    'unsubscribe_reasons' => [
        'too_many_emails' => 'Too many emails',
        'not_relevant' => 'Content not relevant',
        'never_subscribed' => 'Never subscribed',
        'other' => 'Other reason',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for unsubscribe endpoints.
    |
    */
    'rate_limit' => [
        'attempts' => 10,
        'decay_minutes' => 60,
    ],
];

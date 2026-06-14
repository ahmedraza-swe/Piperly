<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform owner (super admin)
    |--------------------------------------------------------------------------
    |
    | Used by PlatformOwnerSeeder and `php artisan platform:ensure-owner`.
    | This account manages plans, tenants, subscriptions, and payments in /admin.
    |
    */

    'owner_email' => env('PLATFORM_OWNER_EMAIL', 'owner@piperly.test'),

    'owner_password' => env('PLATFORM_OWNER_PASSWORD', 'ChangeMe-Platform-2026!'),

    'owner_name' => env('PLATFORM_OWNER_NAME', 'Platform Owner'),

    /*
    |--------------------------------------------------------------------------
    | Public marketing plans (home page)
    |--------------------------------------------------------------------------
    */

    'marketing_product_slugs' => [
        'starter',
        'growth',
    ],

    'default_trial_plan_slug' => env('PLATFORM_DEFAULT_TRIAL_PLAN_SLUG', 'starter-monthly'),

    'support_email' => env('PLATFORM_SUPPORT_EMAIL', 'support@piperly.test'),

];

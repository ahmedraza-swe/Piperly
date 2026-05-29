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

    'owner_email' => env('PLATFORM_OWNER_EMAIL', 'owner@crmercy.test'),

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

    'support_email' => env('PLATFORM_SUPPORT_EMAIL', 'support@crmercy.test'),

];

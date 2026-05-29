<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activity due reminders (email)
    |--------------------------------------------------------------------------
    |
    | When true, the daily `crm:send-activity-due-reminders` command also
    | queues a simple email to the user who logged the activity (user_id).
    |
    */
    'activity_reminder_mail' => (bool) env('CRM_ACTIVITY_REMINDER_MAIL', false),

    /*
    |--------------------------------------------------------------------------
    | In-app CRM notifications (Filament database notifications)
    |--------------------------------------------------------------------------
    |
    | Master switch for observer-driven CRM notifications. Scheduled activity
    | due reminders still respect this flag when sent via CrmInAppNotifier.
    |
    */
    'in_app_notifications' => (bool) env('CRM_IN_APP_NOTIFICATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Database notifications delivery
    |--------------------------------------------------------------------------
    |
    | Filament's database notifications implement ShouldQueue. When your queue
    | worker is not running, nothing appears in the bell unless you either run
    | `php artisan queue:work` or deliver in-app notifications synchronously.
    |
    | Default: synchronous (false) so the bell works in local dev without a worker.
    | Set to true in production if you prefer queue workers to handle the writes.
    |
    */
    'database_notifications_use_queue' => (bool) env('CRM_DATABASE_NOTIFICATIONS_USE_QUEUE', false),

];

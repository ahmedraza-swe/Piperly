<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;
use Illuminate\Console\Command;

/**
 * Sends a single Filament database notification for manual testing.
 */
class TestCrmInAppNotification extends Command
{
    protected $signature = 'crm:test-in-app-notification {email? : User email (defaults to first user in DB)}';

    protected $description = 'Send one test CRM in-app (database) notification to a user';

    public function handle(CrmInAppNotifier $notifier): int
    {
        $email = $this->argument('email');
        $user = $email
            ? User::query()->where('email', $email)->first()
            : User::query()->orderBy('id')->first();

        if (! $user) {
            $this->error($email ? __('No user found for that email.') : __('No users in the database.'));

            return self::FAILURE;
        }

        if (! $notifier->enabled()) {
            $this->warn(__('CRM in-app notifications are disabled (CRM_IN_APP_NOTIFICATIONS=false).'));

            return self::FAILURE;
        }

        $notifier->sendIgnoringActor(
            $user,
            CrmBell::for(
                __('Test notification'),
                CrmBell::twoLineBody(
                    __('If you see this in the bell, database notifications are working.'),
                    __('Sent at :time', ['time' => now()->timezone(config('app.timezone'))->format(config('app.datetime_format'))]),
                ),
                'success',
                'heroicon-o-bell',
                [CrmBell::openAction(config('app.url'), __('Open app'))],
            ),
        );

        $this->info(__('Sent test notification to :email (id :id). Log in as that user and open the tenant dashboard bell.', [
            'email' => $user->email,
            'id' => (string) $user->id,
        ]));

        return self::SUCCESS;
    }
}

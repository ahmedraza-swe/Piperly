<?php

namespace App\Console\Commands;

use App\Filament\Dashboard\Resources\Activities\ActivityResource;
use App\Mail\ActivityDueReminderMailable;
use App\Models\Activity;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendActivityDueReminders extends Command
{
    protected $signature = 'crm:send-activity-due-reminders';

    protected $description = 'Notify users who logged open activities that are due today or overdue (at most once per calendar day per activity).';

    public function handle(CrmInAppNotifier $notifier): int
    {
        $sendMail = (bool) config('crm.activity_reminder_mail', false);

        $query = Activity::query()
            ->openDueWindow()
            ->whereNotNull('user_id')
            ->where(function ($q): void {
                $q->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<', today()->startOfDay());
            })
            ->with(['tenant']);

        $sent = 0;

        $query->chunkById(100, function ($activities) use ($notifier, $sendMail, &$sent): void {
            foreach ($activities as $activity) {
                /** @var Activity $activity */
                $user = User::query()->find($activity->user_id);
                if (! $user) {
                    continue;
                }

                $tenant = $activity->tenant ?? Tenant::query()->find($activity->tenant_id);
                if (! $tenant) {
                    continue;
                }

                $url = ActivityResource::getUrl(
                    'view',
                    ['record' => $activity],
                    isAbsolute: true,
                    panel: 'dashboard',
                    tenant: $tenant,
                );

                $isOverdue = $activity->due_at && $activity->due_at->lt(today()->startOfDay());
                $title = $isOverdue ? __('Overdue activity') : __('Activity due today');
                $tone = $isOverdue ? 'danger' : 'warning';
                $icon = $isOverdue ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-clock';

                $typeLabel = ActivityResource::typeOptions()[$activity->type] ?? $activity->type;
                $dueLine = $activity->due_at
                    ? __('Due: :when', ['when' => $activity->due_at->timezone(config('app.timezone'))->format(config('app.datetime_format'))])
                    : null;

                $detail = collect([
                    __('Type: :type', ['type' => $typeLabel]),
                    $dueLine,
                ])->filter()->implode("\n");

                $notifier->sendIgnoringActor(
                    $user,
                    CrmBell::for(
                        $title,
                        CrmBell::twoLineBody($activity->subject, $detail !== '' ? $detail : __('Open the activity to mark it complete or reschedule.')),
                        $tone,
                        $icon,
                        [CrmBell::openAction($url, __('View activity'))],
                    ),
                );

                if ($sendMail) {
                    Mail::to($user->email)->queue(new ActivityDueReminderMailable($activity, $url, $title));
                }

                $activity->forceFill(['last_reminder_sent_at' => now()])->saveQuietly();
                $sent++;
            }
        });

        $this->info("Queued {$sent} reminder(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Filament database notifications for the tenant CRM workspace.
 *
 * Catalog (in-app bell; optional mail only where already configured elsewhere):
 *
 * Activities
 * - Due today / overdue (scheduled command, once per activity per day) — activity logger (user_id)
 * - New activity logged on a lead (lead owner, if not the logger)
 *
 * Leads
 * - New lead assigned to you (owner on create)
 * - Lead reassigned to you (owner_user_id changed)
 * - Lead marked converted (owner; links to deal when present)
 * - Lead became hot (AI score crossed ≥ 70, owner)
 *
 * Deals
 * - New deal assigned to you (owner on create)
 * - Deal reassigned to you (owner_user_id changed)
 * - Deal moved to another pipeline stage (open deals, owner)
 * - Deal won / lost (owner)
 *
 * Contacts
 * - New contact on a lead you own (lead owner, if not creator)
 *
 * Team / workspace
 * - Someone you invited joined the workspace (inviter)
 * - You were removed from a workspace (removed user)
 *
 * Billing / orders / invitation email already use dedicated mail listeners; they are not duplicated here.
 *
 * Other product notifications (keep on email / existing listeners only for now):
 * - Invitation email to invitee (SendUserInvitationNotification)
 * - Subscription / invoice / order / payment mail listeners under App\Listeners\Subscription and Order
 * - Auth (verify email, password reset) — Laravel defaults
 * - Marketing / system announcements — not part of tenant CRM bell
 *
 * Future CRM enhancements (not implemented): @mentions in notes, SLA breach, duplicate lead detected,
 * integration sync failures, daily digest rollup instead of per-row for high volume.
 */
final class CrmInAppNotifier
{
    public function enabled(): bool
    {
        return (bool) config('crm.in_app_notifications', true);
    }

    /**
     * @param  ?int  $actorUserId  When set, the recipient is skipped if they are the actor (avoid self-noise).
     */
    public function notify(?User $recipient, Notification $notification, ?int $actorUserId = null): void
    {
        if (! $this->enabled() || ! $recipient) {
            return;
        }

        if ($actorUserId !== null && $recipient->id === $actorUserId) {
            return;
        }

        $this->dispatchDatabaseNotification($recipient, $notification);
    }

    public function notifyMany(iterable $users, Notification $notification, ?int $actorUserId = null): void
    {
        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->notify($user, $notification, $actorUserId);
            }
        }
    }

    public function sendIgnoringActor(?User $recipient, Notification $notification): void
    {
        if (! $this->enabled() || ! $recipient) {
            return;
        }

        $this->dispatchDatabaseNotification($recipient, $notification);
    }

    private function dispatchDatabaseNotification(User $recipient, Notification $filamentNotification): void
    {
        $databaseNotification = $filamentNotification->toDatabase();

        if (config('crm.database_notifications_use_queue', false)) {
            $recipient->notify($databaseNotification);

            return;
        }

        NotificationFacade::sendNow($recipient, $databaseNotification);
    }
}

<?php

namespace App\Support;

use App\Filament\Dashboard\Resources\Activities\ActivityResource;
use App\Filament\Dashboard\Resources\Contacts\ContactResource;
use App\Filament\Dashboard\Resources\Deals\DealResource;
use App\Filament\Dashboard\Resources\Leads\LeadResource;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Tenant;

final class TenantResourceUrls
{
    public const PANEL = 'dashboard';

    public static function tenantFor(int $tenantId): ?Tenant
    {
        return Tenant::query()->find($tenantId);
    }

    public static function leadView(Lead $lead, bool $absolute = true): string
    {
        $tenant = $lead->relationLoaded('tenant') ? $lead->getRelation('tenant') : self::tenantFor((int) $lead->tenant_id);
        if (! $tenant) {
            return '#';
        }

        return LeadResource::getUrl('view', ['record' => $lead], isAbsolute: $absolute, panel: self::PANEL, tenant: $tenant);
    }

    public static function dealView(Deal $deal, bool $absolute = true): string
    {
        $tenant = $deal->relationLoaded('tenant') ? $deal->getRelation('tenant') : self::tenantFor((int) $deal->tenant_id);
        if (! $tenant) {
            return '#';
        }

        return DealResource::getUrl('view', ['record' => $deal], isAbsolute: $absolute, panel: self::PANEL, tenant: $tenant);
    }

    public static function activityView(Activity $activity, bool $absolute = true): string
    {
        $tenant = $activity->relationLoaded('tenant') ? $activity->getRelation('tenant') : self::tenantFor((int) $activity->tenant_id);
        if (! $tenant) {
            return '#';
        }

        return ActivityResource::getUrl('view', ['record' => $activity], isAbsolute: $absolute, panel: self::PANEL, tenant: $tenant);
    }

    public static function contactView(Contact $contact, bool $absolute = true): string
    {
        $tenant = $contact->relationLoaded('tenant') ? $contact->getRelation('tenant') : self::tenantFor((int) $contact->tenant_id);
        if (! $tenant) {
            return '#';
        }

        return ContactResource::getUrl('view', ['record' => $contact], isAbsolute: $absolute, panel: self::PANEL, tenant: $tenant);
    }
}

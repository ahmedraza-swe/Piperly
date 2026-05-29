<?php

namespace Tests\Feature\Tenant;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Lead;
use App\Services\TenantCreationService;
use Tests\Feature\FeatureTest;

class CrmCoreFoundationTest extends FeatureTest
{
    public function test_tenant_can_create_lead_contact_and_activity_records(): void
    {
        $user = $this->createUser();
        $tenant = app(TenantCreationService::class)->createTenant($user);
        $stage = $tenant->crmPipelineStages()->firstOrFail();

        $lead = Lead::query()->create([
            'tenant_id' => $tenant->id,
            'pipeline_stage_id' => $stage->id,
            'owner_user_id' => $user->id,
            'title' => 'Acme Expansion Deal',
            'status' => 'open',
            'source' => 'website',
        ]);

        $contact = Contact::query()->create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'first_name' => 'Sarah',
            'last_name' => 'Ahmed',
            'email' => 'sarah@example.test',
            'is_primary' => true,
        ]);

        $activity = Activity::query()->create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'user_id' => $user->id,
            'type' => 'call',
            'subject' => 'Discovery call',
        ]);

        $this->assertSame(1, $tenant->leads()->count());
        $this->assertSame(1, $tenant->contacts()->count());
        $this->assertSame(1, $tenant->activities()->count());
        $this->assertTrue($stage->leads()->whereKey($lead->id)->exists());
        $this->assertTrue($lead->contacts()->whereKey($contact->id)->exists());
        $this->assertTrue($lead->activities()->whereKey($activity->id)->exists());
    }
}

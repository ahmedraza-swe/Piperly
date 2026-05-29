<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Tenant;
use App\Services\CrmPipelineService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CrmDemoDataSeeder extends Seeder
{
    public const SOURCE_MARKER = 'crm_demo_seed';

    public function run(): void
    {
        $tenants = Tenant::query()->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->command?->warn('No tenants found. Log into the app once to create a tenant, then run this seeder again.');

            return;
        }

        foreach ($tenants as $tenant) {
            $this->purgeDemoForTenant($tenant);
            $this->seedTenant($tenant);
            $this->command?->info("CRM demo data seeded for tenant #{$tenant->id} ({$tenant->name}).");
        }
    }

    private function purgeDemoForTenant(Tenant $tenant): void
    {
        $demoLeadIds = Lead::query()
            ->where('tenant_id', $tenant->id)
            ->where('source', self::SOURCE_MARKER)
            ->pluck('id');

        Activity::query()
            ->where('tenant_id', $tenant->id)
            ->where('subject', 'like', '[Demo] %')
            ->delete();

        if ($demoLeadIds->isNotEmpty()) {
            Contact::query()
                ->where('tenant_id', $tenant->id)
                ->whereIn('lead_id', $demoLeadIds)
                ->delete();
        }

        Contact::query()
            ->where('tenant_id', $tenant->id)
            ->where('email', 'like', 'contact.demo.%@example.test')
            ->delete();

        Deal::query()
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) use ($demoLeadIds) {
                $q->where('title', 'like', '[Demo] %')
                    ->orWhereIn('lead_id', $demoLeadIds);
            })
            ->delete();

        Lead::query()
            ->where('tenant_id', $tenant->id)
            ->where('source', self::SOURCE_MARKER)
            ->delete();
    }

    private function seedTenant(Tenant $tenant): void
    {
        $stages = app(CrmPipelineService::class)->ensureDefaultStages($tenant);
        $stageByName = $stages->keyBy(fn ($s) => strtolower($s->name));
        $stageIds = $stages->pluck('id')->all();

        $userIds = $tenant->users()->pluck('users.id')->all();
        $pickOwner = fn (): ?int => $userIds === [] ? null : $userIds[array_rand($userIds)];

        $statuses = ['new', 'contacted', 'qualified', 'nurturing', 'disqualified'];

        $leadTemplates = [
            ['title' => 'Northwind expansion', 'company' => 'Northwind Traders'],
            ['title' => 'Contoso cloud migration', 'company' => 'Contoso Ltd'],
            ['title' => 'Fabrikam pilot', 'company' => 'Fabrikam Inc'],
            ['title' => 'Adventure Works renewal', 'company' => 'Adventure Works'],
            ['title' => 'Litware analytics', 'company' => 'Litware LLC'],
            ['title' => 'Wide World Importers', 'company' => 'Wide World Importers'],
            ['title' => 'Tailspin Toys rollout', 'company' => 'Tailspin Toys'],
            ['title' => 'Blue Yonder logistics', 'company' => 'Blue Yonder'],
            ['title' => 'Alpine Ski House events', 'company' => 'Alpine Ski House'],
            ['title' => 'Fourth Coffee loyalty', 'company' => 'Fourth Coffee'],
            ['title' => 'Margie\'s Travel portal', 'company' => 'Margie\'s Travel'],
            ['title' => 'Graphic Design Institute', 'company' => 'GDI Studio'],
            ['title' => 'Southridge Video CDN', 'company' => 'Southridge Video'],
            ['title' => 'The Phone Company 5G', 'company' => 'The Phone Company'],
            ['title' => 'Woodgrove Bank security', 'company' => 'Woodgrove Bank'],
        ];

        $createdLeadIds = [];

        foreach ($leadTemplates as $index => $tpl) {
            $createdAt = Carbon::now()->subDays(random_int(0, 13))->subHours(random_int(0, 20));
            $status = $statuses[$index % count($statuses)];
            $aiScore = random_int(25, 95);
            $owner = random_int(0, 4) === 0 ? null : $pickOwner();

            $lastContacted = match (random_int(0, 3)) {
                0 => null,
                1 => $createdAt->copy()->addDay(),
                2 => Carbon::now()->subDays(random_int(1, 5)),
                default => Carbon::now()->subDays(random_int(8, 20)),
            };

            $lead = Lead::query()->create([
                'tenant_id' => $tenant->id,
                'pipeline_stage_id' => $stageIds[array_rand($stageIds)],
                'owner_user_id' => $owner,
                'title' => $tpl['title'],
                'company_name' => $tpl['company'],
                'email' => 'demo.lead.'.($index + 1).'@example.test',
                'phone' => '+1-555-01'.str_pad((string) (80 + $index), 2, '0', STR_PAD_LEFT),
                'status' => $status,
                'source' => self::SOURCE_MARKER,
                'value' => random_int(2, 120) * 500 + random_int(0, 99),
                'ai_score' => $aiScore,
                'notes' => 'Seeded demo lead for dashboard stats.',
                'last_contacted_at' => $lastContacted,
                'converted_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $createdLeadIds[] = $lead->id;
        }

        // Extra volume for stats (hot / unassigned / stale)
        for ($i = 0; $i < 18; $i++) {
            $createdAt = Carbon::now()->subDays(random_int(0, 13));
            $hot = $i % 5 === 0;
            $aiScore = $hot ? random_int(70, 95) : random_int(30, 69);
            $owner = $i % 4 === 0 ? null : $pickOwner();
            $lastContacted = $i % 6 === 0
                ? Carbon::now()->subDays(random_int(10, 30))
                : Carbon::now()->subDays(random_int(0, 3));

            $lead = Lead::query()->create([
                'tenant_id' => $tenant->id,
                'pipeline_stage_id' => $stageIds[array_rand($stageIds)],
                'owner_user_id' => $owner,
                'title' => 'Demo lead #'.($i + 1),
                'company_name' => 'Demo Company '.($i + 1),
                'email' => 'bulk.demo.'.($i + 1).'@example.test',
                'status' => $statuses[array_rand($statuses)],
                'source' => self::SOURCE_MARKER,
                'value' => random_int(1, 80) * 250,
                'ai_score' => $aiScore,
                'notes' => null,
                'last_contacted_at' => $lastContacted,
                'converted_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $createdLeadIds[] = $lead->id;
        }

        $firstNames = ['Alex', 'Jordan', 'Taylor', 'Riley', 'Morgan', 'Casey', 'Quinn', 'Avery'];
        $lastNames = ['Nguyen', 'Patel', 'Garcia', 'Silva', 'Khan', 'Berg', 'Lopez', 'Park'];
        $slice = array_slice($createdLeadIds, 0, 22);
        foreach ($slice as $index => $leadId) {
            $count = ($index % 4 === 0) ? 2 : 1;
            for ($j = 0; $j < $count; $j++) {
                Contact::query()->create([
                    'tenant_id' => $tenant->id,
                    'lead_id' => $leadId,
                    'first_name' => $firstNames[($index + $j) % count($firstNames)],
                    'last_name' => $lastNames[($index + $j) % count($lastNames)],
                    'job_title' => $j === 0 ? 'VP Sales' : 'Procurement',
                    'email' => 'contact.demo.'.$tenant->id.'.'.$leadId.'.'.$j.'@example.test',
                    'phone' => '+1-555-'.str_pad((string) (200 + $index + $j), 4, '0', STR_PAD_LEFT),
                    'is_primary' => $j === 0,
                    'notes' => $j === 0 ? 'Primary stakeholder for this lead.' : null,
                ]);
            }
        }

        for ($s = 0; $s < 3; $s++) {
            Contact::query()->create([
                'tenant_id' => $tenant->id,
                'lead_id' => null,
                'first_name' => ['Sam', 'Jamie', 'Drew'][$s],
                'last_name' => 'Standalone',
                'job_title' => 'Founder',
                'email' => 'contact.demo.'.$tenant->id.'.standalone.'.$s.'@example.test',
                'phone' => '+1-555-9'.str_pad((string) (100 + $s), 3, '0', STR_PAD_LEFT),
                'is_primary' => false,
                'notes' => 'Demo contact without a linked lead.',
            ]);
        }

        // Converted leads + open deals
        $convertCount = min(8, count($createdLeadIds));
        $prospectStage = $stageByName->get('prospecting')?->id ?? $stages->first()->id;
        $qualifiedStage = $stageByName->get('qualified')?->id ?? $stages->skip(1)->first()->id;
        $wonStage = $stageByName->get('won')?->id;
        $lostStage = $stageByName->get('lost')?->id;

        for ($c = 0; $c < $convertCount; $c++) {
            $leadId = $createdLeadIds[$c];
            $lead = Lead::query()->find($leadId);
            if (! $lead) {
                continue;
            }

            $dealStage = match ($c % 5) {
                0, 1 => $prospectStage,
                2, 3 => $qualifiedStage,
                default => $stageIds[array_rand($stageIds)],
            };

            $dealStatus = match ($c % 7) {
                5 => 'won',
                6 => 'lost',
                default => 'open',
            };

            $deal = Deal::query()->create([
                'tenant_id' => $tenant->id,
                'lead_id' => $lead->id,
                'pipeline_stage_id' => $dealStatus === 'won' && $wonStage
                    ? $wonStage
                    : ($dealStatus === 'lost' && $lostStage ? $lostStage : $dealStage),
                'owner_user_id' => $lead->owner_user_id,
                'title' => '[Demo] '.$lead->title,
                'company_name' => $lead->company_name,
                'status' => $dealStatus,
                'value' => $lead->value ?? random_int(20, 150) * 100,
                'closed_at' => in_array($dealStatus, ['won', 'lost'], true) ? Carbon::now()->subDays(random_int(1, 10)) : null,
                'created_at' => $lead->created_at->copy()->addDays(random_int(0, 3)),
                'updated_at' => now(),
            ]);

            $lead->update([
                'converted_at' => $deal->created_at,
                'status' => 'qualified',
            ]);
        }

        // Standalone deals (no lead)
        for ($d = 0; $d < 6; $d++) {
            Deal::query()->create([
                'tenant_id' => $tenant->id,
                'lead_id' => null,
                'pipeline_stage_id' => $stageIds[array_rand($stageIds)],
                'owner_user_id' => $pickOwner(),
                'title' => '[Demo] Inbound opportunity '.($d + 1),
                'company_name' => 'Standalone Co '.($d + 1),
                'status' => $d % 4 === 0 ? 'open' : 'open',
                'value' => random_int(5, 90) * 1000,
                'created_at' => Carbon::now()->subDays(random_int(0, 10)),
                'updated_at' => now(),
            ]);
        }

        $actorId = $pickOwner() ?? $tenant->users()->first()?->id;
        if ($actorId) {
            $types = ['call', 'meeting', 'email', 'task', 'note'];
            $subjects = ['Follow-up', 'Check-in', 'Proposal review', 'Intro call', 'Pricing discussion'];
            $demoLeadsForActivity = Lead::query()
                ->where('tenant_id', $tenant->id)
                ->where('source', self::SOURCE_MARKER)
                ->inRandomOrder()
                ->limit(20)
                ->get();

            foreach ($demoLeadsForActivity as $i => $lead) {
                $completed = $i % 5 === 0 ? Carbon::now()->subDays(random_int(1, 4)) : null;
                $dueAt = $completed
                    ? null
                    : (random_int(0, 2) === 0
                        ? Carbon::now()->subDays(random_int(1, 6))
                        : Carbon::now()->addDays(random_int(0, 8)));

                Activity::query()->create([
                    'tenant_id' => $tenant->id,
                    'lead_id' => $lead->id,
                    'contact_id' => null,
                    'user_id' => $actorId,
                    'type' => $types[$i % count($types)],
                    'subject' => '[Demo] '.$subjects[$i % count($subjects)],
                    'description' => 'Seeded demo activity.',
                    'due_at' => $dueAt,
                    'completed_at' => $completed,
                    'created_at' => Carbon::now()->subDays(random_int(0, 12)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

<?php

namespace App\Filament\Dashboard\Resources\Activities\Pages;

use App\Filament\Dashboard\Resources\Activities\ActivityResource;
use App\Models\Contact;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()->id;
        $data['user_id'] = auth()->id();
        $data['lead_id'] = filled($data['lead_id'] ?? null) ? (int) $data['lead_id'] : null;
        $data['contact_id'] = filled($data['contact_id'] ?? null) ? (int) $data['contact_id'] : null;

        if ($data['lead_id'] === null && $data['contact_id'] !== null) {
            $contact = Contact::query()
                ->where('tenant_id', $data['tenant_id'])
                ->find($data['contact_id']);

            if ($contact?->lead_id) {
                $data['lead_id'] = $contact->lead_id;
            }
        }

        return $data;
    }
}

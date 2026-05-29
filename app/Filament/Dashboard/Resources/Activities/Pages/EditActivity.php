<?php

namespace App\Filament\Dashboard\Resources\Activities\Pages;

use App\Filament\Dashboard\Resources\Activities\ActivityResource;
use App\Models\Contact;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['lead_id'] = filled($data['lead_id'] ?? null) ? (int) $data['lead_id'] : null;
        $data['contact_id'] = filled($data['contact_id'] ?? null) ? (int) $data['contact_id'] : null;

        if ($data['lead_id'] === null && $data['contact_id'] !== null) {
            $contact = Contact::query()
                ->where('tenant_id', $this->record->tenant_id)
                ->find($data['contact_id']);

            if ($contact?->lead_id) {
                $data['lead_id'] = $contact->lead_id;
            }
        }

        return $data;
    }
}

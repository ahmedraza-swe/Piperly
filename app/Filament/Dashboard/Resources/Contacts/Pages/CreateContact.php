<?php

namespace App\Filament\Dashboard\Resources\Contacts\Pages;

use App\Filament\Dashboard\Resources\Contacts\ContactResource;
use App\Models\Contact;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['email'] ?? null)) {
            $exists = Contact::query()
                ->where('tenant_id', Filament::getTenant()->id)
                ->where('email', $data['email'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'email' => __('A contact with this email already exists in this workspace.'),
                ]);
            }
        }

        $data['tenant_id'] = Filament::getTenant()->id;
        $data['lead_id'] = filled($data['lead_id'] ?? null) ? (int) $data['lead_id'] : null;

        return $data;
    }
}

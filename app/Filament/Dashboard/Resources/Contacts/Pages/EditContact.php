<?php

namespace App\Filament\Dashboard\Resources\Contacts\Pages;

use App\Filament\Dashboard\Resources\Contacts\ContactResource;
use App\Models\Contact;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['lead_id'] = filled($data['lead_id'] ?? null) ? (int) $data['lead_id'] : null;

        if (filled($data['email'] ?? null)) {
            $exists = Contact::query()
                ->where('tenant_id', $this->record->tenant_id)
                ->where('email', $data['email'])
                ->whereKeyNot($this->record->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'email' => __('A contact with this email already exists in this workspace.'),
                ]);
            }
        }

        return $data;
    }
}

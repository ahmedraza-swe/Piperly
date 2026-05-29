<?php

namespace App\Filament\Dashboard\Resources\Leads\Pages;

use App\Filament\Dashboard\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['email'] ?? null) || filled($data['phone'] ?? null)) {
            $duplicateLead = Lead::query()
                ->where('tenant_id', $this->record->tenant_id)
                ->whereKeyNot($this->record->id)
                ->where(function ($query) use ($data) {
                    if (filled($data['email'] ?? null)) {
                        $query->orWhere('email', $data['email']);
                    }

                    if (filled($data['phone'] ?? null)) {
                        $query->orWhere('phone', $data['phone']);
                    }
                })
                ->first();

            if ($duplicateLead) {
                throw ValidationException::withMessages([
                    'email' => __('Duplicate lead detected with same email/phone in this workspace.'),
                ]);
            }
        }

        return $data;
    }
}

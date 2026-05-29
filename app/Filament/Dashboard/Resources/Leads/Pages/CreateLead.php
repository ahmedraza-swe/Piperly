<?php

namespace App\Filament\Dashboard\Resources\Leads\Pages;

use App\Filament\Dashboard\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['email'] ?? null) || filled($data['phone'] ?? null)) {
            $duplicateLead = Lead::query()
                ->where('tenant_id', Filament::getTenant()->id)
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

        $data['tenant_id'] = Filament::getTenant()->id;

        return $data;
    }
}

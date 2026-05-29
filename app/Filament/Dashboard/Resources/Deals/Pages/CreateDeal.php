<?php

namespace App\Filament\Dashboard\Resources\Deals\Pages;

use App\Filament\Dashboard\Resources\Deals\DealResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()->id;

        return $data;
    }
}

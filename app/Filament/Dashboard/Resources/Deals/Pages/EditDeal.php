<?php

namespace App\Filament\Dashboard\Resources\Deals\Pages;

use App\Filament\Dashboard\Resources\Deals\DealResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeal extends EditRecord
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

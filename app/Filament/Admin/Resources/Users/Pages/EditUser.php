<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

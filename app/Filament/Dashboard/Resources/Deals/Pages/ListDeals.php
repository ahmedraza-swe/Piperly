<?php

namespace App\Filament\Dashboard\Resources\Deals\Pages;

use App\Filament\Dashboard\Resources\Deals\DealResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDeals extends ListRecords
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kanban')
                ->label(__('Board'))
                ->icon('heroicon-o-view-columns')
                ->url(DealResource::getUrl('kanban'))
                ->color('gray'),
            CreateAction::make()->label(__('Add Deal')),
        ];
    }

    public function getTabs(): array
    {
        return [
            __('All') => Tab::make(),
            __('Open') => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'open')),
            __('Won') => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'won')),
            __('Lost') => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'lost')),
        ];
    }
}

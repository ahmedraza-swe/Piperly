<?php

namespace App\Filament\Dashboard\Resources\Leads\Pages;

use App\Filament\Dashboard\Resources\Leads\LeadResource;
use App\Filament\Dashboard\Resources\Leads\Widgets\LeadOverview;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    public function mount(): void
    {
        parent::mount();

        $savedDefaultTab = auth()->user()?->userParameters()
            ->where('name', 'leads.default_tab')
            ->value('value');

        if (filled($savedDefaultTab)) {
            $this->activeTab = $savedDefaultTab;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Add Lead')),
            Action::make('save-default-view')
                ->label(__('Save Default View'))
                ->form([
                    Select::make('default_tab')
                        ->label(__('Default Leads View'))
                        ->required()
                        ->options([
                            'All' => __('All'),
                            'Hot' => __('Hot'),
                            'Unassigned' => __('Unassigned'),
                            'No Activity' => __('No Activity'),
                        ]),
                ])
                ->action(function (array $data): void {
                    auth()->user()?->userParameters()->updateOrCreate(
                        ['name' => 'leads.default_tab'],
                        ['value' => $data['default_tab']]
                    );

                    Notification::make()
                        ->title(__('Default leads view saved'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LeadOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()->label(__('All')),
            'Hot' => Tab::make()->label(__('Hot'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereNotNull('ai_score')
                    ->where('ai_score', '>=', 70)),
            'Unassigned' => Tab::make()->label(__('Unassigned'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereNull('owner_user_id')),
            'No Activity' => Tab::make()->label(__('No Activity'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where(function (Builder $query) {
                        $query->whereNull('last_contacted_at')
                            ->orWhere('last_contacted_at', '<=', now()->subDays(7));
                    })),
        ];
    }
}

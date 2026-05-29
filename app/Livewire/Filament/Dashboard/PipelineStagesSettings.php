<?php

namespace App\Livewire\Filament\Dashboard;

use App\Models\CrmPipelineStage;
use App\Services\CrmPipelineService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class PipelineStagesSettings extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function mount(CrmPipelineService $crmPipelineService): void
    {
        $crmPipelineService->ensureDefaultStages(Filament::getTenant());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => CrmPipelineStage::query()
                ->where('tenant_id', Filament::getTenant()->id)
                ->orderBy('sort_order'))
            ->columns([
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->sortable(),
                TextInputColumn::make('name')
                    ->label(__('Stage name'))
                    ->rules(['required', 'max:120'])
                    ->updateStateUsing(function (CrmPipelineStage $record, ?string $state, CrmPipelineService $crmPipelineService): void {
                        if (blank($state)) {
                            return;
                        }

                        $crmPipelineService->updateStageName($record, $state);

                        Notification::make()
                            ->title(__('Stage updated'))
                            ->success()
                            ->send();
                    }),
                TextColumn::make('leads_count')
                    ->counts('leads')
                    ->label(__('Leads')),
                TextColumn::make('deals_count')
                    ->counts('deals')
                    ->label(__('Deals')),
            ])
            ->headerActions([
                Action::make('addStage')
                    ->label(__('Add stage'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('name')
                            ->label(__('Stage name'))
                            ->required()
                            ->maxLength(120),
                    ])
                    ->action(function (array $data, CrmPipelineService $crmPipelineService): void {
                        $crmPipelineService->createStage(Filament::getTenant(), $data['name']);

                        Notification::make()
                            ->title(__('Stage added'))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('moveUp')
                    ->label(__('Move up'))
                    ->icon('heroicon-o-arrow-up')
                    ->action(fn (CrmPipelineStage $record) => $this->moveStage($record, -1)),
                Action::make('moveDown')
                    ->label(__('Move down'))
                    ->icon('heroicon-o-arrow-down')
                    ->action(fn (CrmPipelineStage $record) => $this->moveStage($record, 1)),
                Action::make('delete')
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(__('Stages in use by leads or deals cannot be deleted.'))
                    ->visible(fn (CrmPipelineStage $record, CrmPipelineService $crmPipelineService): bool => $crmPipelineService->canDeleteStage($record))
                    ->action(function (CrmPipelineStage $record, CrmPipelineService $crmPipelineService): void {
                        if ($crmPipelineService->deleteStage($record)) {
                            Notification::make()
                                ->title(__('Stage deleted'))
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('Cannot delete stage'))
                            ->body(__('Remove or reassign leads and deals first.'))
                            ->danger()
                            ->send();
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('No pipeline stages'))
            ->emptyStateDescription(__('Add a stage to organize leads and deals.'));
    }

    private function moveStage(CrmPipelineStage $record, int $direction): void
    {
        $tenant = Filament::getTenant();
        $stages = CrmPipelineStage::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('sort_order')
            ->pluck('id')
            ->all();

        $index = array_search($record->id, $stages, true);
        if ($index === false) {
            return;
        }

        $swapIndex = $index + $direction;
        if ($swapIndex < 0 || $swapIndex >= count($stages)) {
            return;
        }

        [$stages[$index], $stages[$swapIndex]] = [$stages[$swapIndex], $stages[$index]];

        app(CrmPipelineService::class)->reorderStages($tenant, $stages);

        Notification::make()
            ->title(__('Order updated'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.filament.dashboard.pipeline-stages-settings');
    }
}

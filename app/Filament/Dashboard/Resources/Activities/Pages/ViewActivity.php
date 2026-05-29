<?php

namespace App\Filament\Dashboard\Resources\Activities\Pages;

use App\Filament\Dashboard\Resources\Activities\ActivityResource;
use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_complete')
                ->label(__('Mark complete'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->completed_at === null)
                ->requiresConfirmation()
                ->action(function (): void {
                    /** @var Activity $activity */
                    $activity = $this->record;
                    $activity->update(['completed_at' => now()]);

                    Notification::make()
                        ->title(__('Marked complete'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                    $this->dispatch('$refresh');
                }),
            Action::make('reopen')
                ->label(__('Reopen'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->visible(fn (): bool => $this->record->completed_at !== null)
                ->requiresConfirmation()
                ->action(function (): void {
                    /** @var Activity $activity */
                    $activity = $this->record;
                    $activity->update(['completed_at' => null]);

                    Notification::make()
                        ->title(__('Activity reopened'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                    $this->dispatch('$refresh');
                }),
            EditAction::make()->label(__('Edit')),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Dashboard\Resources\Contacts\Pages;

use App\Filament\Dashboard\Resources\Contacts\ContactResource;
use App\Models\Activity;
use App\Models\Contact;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewContact extends ViewRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('log-activity')
                ->label(__('Log activity'))
                ->form([
                    Select::make('type')
                        ->required()
                        ->options([
                            'call' => __('Call'),
                            'meeting' => __('Meeting'),
                            'email' => __('Email'),
                            'task' => __('Task'),
                            'note' => __('Note'),
                        ])
                        ->default('note'),
                    TextInput::make('subject')
                        ->required(),
                    Textarea::make('description'),
                    DateTimePicker::make('due_at')
                        ->label(__('Due at')),
                ])
                ->action(function (array $data): void {
                    /** @var Contact $contact */
                    $contact = $this->record;

                    Activity::query()->create([
                        'tenant_id' => $contact->tenant_id,
                        'lead_id' => $contact->lead_id,
                        'contact_id' => $contact->id,
                        'user_id' => auth()->id(),
                        'type' => $data['type'],
                        'subject' => $data['subject'],
                        'description' => $data['description'] ?? null,
                        'due_at' => $data['due_at'] ?? null,
                    ]);

                    if ($contact->lead_id) {
                        $contact->lead()->update([
                            'last_contacted_at' => now(),
                        ]);
                    }

                    Notification::make()
                        ->title(__('Activity logged'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                    $this->dispatch('$refresh');
                }),
            EditAction::make()->label(__('Edit contact')),
            DeleteAction::make()->label(__('Delete contact')),
        ];
    }
}

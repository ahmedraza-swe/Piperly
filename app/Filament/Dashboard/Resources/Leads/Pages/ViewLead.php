<?php

namespace App\Filament\Dashboard\Resources\Leads\Pages;

use App\Filament\Dashboard\Resources\Leads\LeadResource;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Services\CrmPipelineService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('log-activity')
                ->label(__('Log Activity'))
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
                        ->label(__('Due At')),
                ])
                ->action(function (array $data): void {
                    Activity::query()->create([
                        'tenant_id' => $this->record->tenant_id,
                        'lead_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'type' => $data['type'],
                        'subject' => $data['subject'],
                        'description' => $data['description'] ?? null,
                        'due_at' => $data['due_at'] ?? null,
                    ]);

                    $this->record->update([
                        'last_contacted_at' => now(),
                    ]);

                    Notification::make()
                        ->title(__('Activity logged'))
                        ->success()
                        ->send();
                }),
            Action::make('convert-to-deal')
                ->label(__('Convert to Deal'))
                ->color('success')
                ->visible(fn (): bool => $this->record->converted_at === null)
                ->action(function (): void {
                    $stages = app(CrmPipelineService::class)->ensureDefaultStages($this->record->tenant);
                    $defaultStageId = $this->record->pipeline_stage_id ?: $stages->first()?->getKey();

                    $deal = Deal::query()->create([
                        'tenant_id' => $this->record->tenant_id,
                        'lead_id' => $this->record->id,
                        'pipeline_stage_id' => $defaultStageId,
                        'owner_user_id' => $this->record->owner_user_id,
                        'title' => $this->record->title,
                        'company_name' => $this->record->company_name,
                        'status' => 'open',
                        'value' => $this->record->value,
                    ]);

                    $this->record->update([
                        'converted_at' => now(),
                        'status' => 'qualified',
                    ]);

                    Contact::query()
                        ->where('tenant_id', $this->record->tenant_id)
                        ->where('lead_id', $this->record->id)
                        ->update(['lead_id' => $this->record->id]);

                    Notification::make()
                        ->title(__('Lead converted to deal #:id', ['id' => $deal->id]))
                        ->success()
                        ->send();
                }),
            Action::make('ai-score')
                ->label(__('AI Score Lead'))
                ->action(function (): void {
                    $this->record->update([
                        'ai_score' => random_int(35, 95),
                    ]);

                    Notification::make()
                        ->title(__('AI score updated'))
                        ->success()
                        ->send();
                }),
            Action::make('ai-draft')
                ->label(__('AI Draft Follow-up'))
                ->action(function (): void {
                    Notification::make()
                        ->title(__('AI draft is ready (placeholder)'))
                        ->body(__('Next step: connect OpenAI and open a draft modal.'))
                        ->success()
                        ->send();
                }),
        ];
    }
}

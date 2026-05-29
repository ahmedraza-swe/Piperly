<?php

namespace App\Filament\Dashboard\Resources\Leads;

use App\Filament\Dashboard\Resources\Leads\Pages\CreateLead;
use App\Filament\Dashboard\Resources\Leads\Pages\EditLead;
use App\Filament\Dashboard\Resources\Leads\Pages\ListLeads;
use App\Filament\Dashboard\Resources\Contacts\ContactResource;
use App\Filament\Dashboard\Resources\Leads\Pages\ViewLead;
use App\Filament\Dashboard\Support\TableRecordActions;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\CrmPipelineStage;
use App\Models\Lead;
use App\Support\LocaleMoney;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    public static function getNavigationGroup(): ?string
    {
        return __('Leads');
    }

    public static function getNavigationLabel(): string
    {
        return __('Leads');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Lead Information'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Lead Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('company_name')
                            ->label(__('Company'))
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),
                        Select::make('status')
                            ->options([
                                'new' => __('New'),
                                'contacted' => __('Contacted'),
                                'qualified' => __('Qualified'),
                                'nurturing' => __('Nurturing'),
                                'disqualified' => __('Disqualified'),
                            ])
                            ->default('new')
                            ->required(),
                        TextInput::make('source')
                            ->label(__('Source'))
                            ->placeholder(__('Website, Referral, Campaign...'))
                            ->maxLength(120),
                        TextInput::make('value')
                            ->numeric()
                            ->prefix('$')
                            ->label(__('Expected Deal Value')),
                        Select::make('pipeline_stage_id')
                            ->label(__('Pipeline Stage'))
                            ->options(function () {
                                return CrmPipelineStage::query()
                                    ->where('tenant_id', Filament::getTenant()->id)
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id');
                            }),
                        Select::make('owner_user_id')
                            ->label(__('Lead Owner'))
                            ->options(function () {
                                return Filament::getTenant()->users()
                                    ->select('users.name', 'users.id')
                                    ->pluck('users.name', 'users.id');
                            })
                            ->searchable(),
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Lead'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_name')
                    ->label(__('Company'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'qualified' => 'success',
                        'nurturing' => 'warning',
                        'disqualified' => 'danger',
                        default => 'primary',
                    }),
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state): ?string => LocaleMoney::currency($state !== null ? (float) $state : null))
                    ->sortable(),
                TextColumn::make('ai_score')
                    ->label(__('AI Score'))
                    ->badge()
                    ->color(fn (?int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'gray'))
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => __('New'),
                        'contacted' => __('Contacted'),
                        'qualified' => __('Qualified'),
                        'nurturing' => __('Nurturing'),
                        'disqualified' => __('Disqualified'),
                    ]),
                SelectFilter::make('source')
                    ->options(function () {
                        return Lead::query()
                            ->where('tenant_id', Filament::getTenant()->id)
                            ->whereNotNull('source')
                            ->where('source', '!=', '')
                            ->distinct()
                            ->orderBy('source')
                            ->pluck('source', 'source');
                    }),
                SelectFilter::make('owner_user_id')
                    ->label(__('Owner'))
                    ->options(function () {
                        return Filament::getTenant()->users()
                            ->select('users.name', 'users.id')
                            ->pluck('users.name', 'users.id');
                    }),
                Filter::make('created_between')
                    ->label(__('Created Date'))
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(__('From')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label(__('Until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('uncontacted_for_week')
                    ->label(__('No Activity (7 Days)'))
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query) {
                        $query->whereNull('last_contacted_at')
                            ->orWhere('last_contacted_at', '<=', now()->subDays(7));
                    })),
            ])
            ->recordActions([
                ...TableRecordActions::viewEditDelete(
                    __('View lead'),
                    __('Edit lead'),
                    __('Delete lead'),
                ),
                ActionGroup::make([
                    Action::make('ai-score')
                        ->label(__('AI Score Lead'))
                        ->action(function (Lead $record): void {
                            $record->update([
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
                                ->body(__('Next step: connect OpenAI endpoint and show generated draft modal.'))
                                ->success()
                                ->send();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip(__('More')),
            ])
            ->recordActionsColumnLabel(__('Actions'))
            ->toolbarActions([
                Action::make('import-csv')
                    ->label(__('Import CSV'))
                    ->form([
                        FileUpload::make('csv')
                            ->label(__('CSV File'))
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->directory('imports/leads')
                            ->disk('local')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $path = $data['csv'] ?? null;

                        if (! $path || ! Storage::disk('local')->exists($path)) {
                            Notification::make()
                                ->title(__('CSV file not found'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $fullPath = Storage::disk('local')->path($path);
                        $rows = array_map('str_getcsv', file($fullPath));

                        if (count($rows) < 2) {
                            Notification::make()
                                ->title(__('CSV has no data rows'))
                                ->warning()
                                ->send();

                            return;
                        }

                        $header = array_map(fn ($col) => strtolower(trim((string) $col)), $rows[0]);
                        $createdCount = 0;
                        $tenantId = Filament::getTenant()->id;

                        foreach (array_slice($rows, 1) as $row) {
                            if (count(array_filter($row, fn ($value) => filled($value))) === 0) {
                                continue;
                            }

                            $mapped = array_combine($header, array_pad($row, count($header), null));

                            Lead::query()->create([
                                'tenant_id' => $tenantId,
                                'title' => $mapped['title'] ?? $mapped['name'] ?? __('Imported Lead'),
                                'company_name' => $mapped['company_name'] ?? $mapped['company'] ?? null,
                                'email' => $mapped['email'] ?? null,
                                'phone' => $mapped['phone'] ?? null,
                                'status' => $mapped['status'] ?? 'new',
                                'source' => $mapped['source'] ?? null,
                                'value' => is_numeric($mapped['value'] ?? null) ? (float) $mapped['value'] : null,
                                'notes' => $mapped['notes'] ?? null,
                            ]);
                            $createdCount++;
                        }

                        Notification::make()
                            ->title(__('CSV import completed'))
                            ->body(__('Created :count leads.', ['count' => $createdCount]))
                            ->success()
                            ->send();
                    }),
                BulkAction::make('assign-owner')
                    ->label(__('Assign Owner'))
                    ->form([
                        Select::make('owner_user_id')
                            ->label(__('Owner'))
                            ->required()
                            ->options(fn () => Filament::getTenant()->users()
                                ->select('users.name', 'users.id')
                                ->pluck('users.name', 'users.id')),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $records->each(fn (Lead $lead) => $lead->update([
                            'owner_user_id' => $data['owner_user_id'],
                        ]));
                    }),
                BulkAction::make('change-status')
                    ->label(__('Change Status'))
                    ->form([
                        Select::make('status')
                            ->required()
                            ->options([
                                'new' => __('New'),
                                'contacted' => __('Contacted'),
                                'qualified' => __('Qualified'),
                                'nurturing' => __('Nurturing'),
                                'disqualified' => __('Disqualified'),
                            ]),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $records->each(fn (Lead $lead) => $lead->update([
                            'status' => $data['status'],
                        ]));
                    }),
                DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'view' => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Lead Profile'))
                    ->schema([
                        TextEntry::make('title')->label(__('Lead Name')),
                        TextEntry::make('company_name')->label(__('Company')),
                        TextEntry::make('email')->label(__('Email')),
                        TextEntry::make('phone')->label(__('Phone')),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'qualified' => 'success',
                                'nurturing' => 'warning',
                                'disqualified' => 'danger',
                                default => 'primary',
                            }),
                        TextEntry::make('source')->label(__('Source')),
                        TextEntry::make('value')
                            ->label(__('Expected Value'))
                            ->formatStateUsing(fn ($state): ?string => LocaleMoney::currency($state !== null ? (float) $state : null)),
                        TextEntry::make('ai_score')->label(__('AI Score')),
                        TextEntry::make('owner.name')->label(__('Owner')),
                    ])->columns(3),
                Section::make(__('Activity Timeline'))
                    ->schema([
                        TextEntry::make('activities_timeline')
                            ->label('')
                            ->state(function (Lead $record): string {
                                $items = $record->activities()
                                    ->latest()
                                    ->limit(8)
                                    ->get()
                                    ->map(function (Activity $activity): string {
                                        return sprintf(
                                            '[%s] %s - %s',
                                            $activity->created_at?->format('d/m/Y H:i'),
                                            ucfirst((string) $activity->type),
                                            $activity->subject
                                        );
                                    })
                                    ->implode("\n");

                                return $items !== '' ? $items : __('No activities yet.');
                            })
                            ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                            ->html(),
                    ]),
                Section::make(__('Notes'))
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder(__('No notes yet.')),
                    ]),
                Section::make(__('Linked Contacts'))
                    ->schema([
                        TextEntry::make('contacts_list')
                            ->label('')
                            ->state(function (Lead $record): string {
                                $html = $record->contacts()
                                    ->latest()
                                    ->limit(8)
                                    ->get()
                                    ->map(function (Contact $contact): string {
                                        $name = trim(($contact->first_name ?? '').' '.($contact->last_name ?? ''));
                                        $name = $name !== '' ? e($name) : e(__('Unnamed Contact'));
                                        $meta = e($contact->email ?: __('no email'));
                                        $url = e(ContactResource::getUrl('view', ['record' => $contact->id]));

                                        return '<a class="text-primary-600 hover:underline" href="'.$url.'">'.$name.'</a>'
                                            .' <span class="text-gray-500">('.$meta.')</span>';
                                    })
                                    ->implode('<br>');

                                return $html !== '' ? $html : e(__('No linked contacts.'));
                            })
                            ->html(),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', Filament::getTenant()->id);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }
}

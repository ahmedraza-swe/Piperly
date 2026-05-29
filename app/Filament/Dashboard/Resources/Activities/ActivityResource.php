<?php

namespace App\Filament\Dashboard\Resources\Activities;

use App\Filament\Dashboard\Resources\Activities\Pages\CreateActivity;
use App\Filament\Dashboard\Resources\Activities\Pages\EditActivity;
use App\Filament\Dashboard\Resources\Activities\Pages\ListActivities;
use App\Filament\Dashboard\Resources\Activities\Pages\ViewActivity;
use App\Filament\Dashboard\Resources\Contacts\ContactResource;
use App\Filament\Dashboard\Support\TableRecordActions;
use App\Filament\Dashboard\Resources\Leads\LeadResource;
use App\Models\Activity;
use App\Models\Contact;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 0;

    public static function getNavigationGroup(): ?string
    {
        return __('Activities');
    }

    public static function getNavigationLabel(): string
    {
        return __('Activities');
    }

    public static function getModelLabel(): string
    {
        return __('Activity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activities');
    }

    public static function typeOptions(): array
    {
        return [
            'call' => __('Call'),
            'meeting' => __('Meeting'),
            'email' => __('Email'),
            'task' => __('Task'),
            'note' => __('Note'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Activity'))
                    ->schema([
                        Select::make('type')
                            ->label(__('Type'))
                            ->options(static::typeOptions())
                            ->required()
                            ->default('note'),
                        TextInput::make('subject')
                            ->label(__('Subject'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(4)
                            ->columnSpanFull(),
                        DateTimePicker::make('due_at')
                            ->label(__('Due at'))
                            ->nullable(),
                        DateTimePicker::make('completed_at')
                            ->label(__('Completed at'))
                            ->nullable(),
                        Select::make('lead_id')
                            ->label(__('Linked lead'))
                            ->relationship(
                                name: 'lead',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('tenant_id', Filament::getTenant()->id)
                                    ->orderBy('title'),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('contact_id')
                            ->label(__('Linked contact'))
                            ->relationship(
                                name: 'contact',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('tenant_id', Filament::getTenant()->id)
                                    ->orderBy('first_name')
                                    ->orderBy('last_name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Contact $record): string => $record->full_name
                                .($record->email ? ' ('.$record->email.')' : ''))
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::typeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'call' => 'info',
                        'meeting' => 'warning',
                        'email' => 'success',
                        'task' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('lead.title')
                    ->label(__('Lead'))
                    ->placeholder('—')
                    ->url(fn (Activity $record): ?string => $record->lead_id
                        ? LeadResource::getUrl('view', ['record' => $record->lead_id])
                        : null)
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('contact.full_name')
                    ->label(__('Contact'))
                    ->placeholder('—')
                    ->url(fn (Activity $record): ?string => $record->contact_id
                        ? ContactResource::getUrl('view', ['record' => $record->contact_id])
                        : null)
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label(__('Logged by'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('due_at')
                    ->label(__('Due'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('completed_at')
                    ->label(__('Done'))
                    ->boolean()
                    ->getStateUsing(fn (Activity $record): bool => $record->completed_at !== null),
                TextColumn::make('created_at')
                    ->label(__('Logged'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(static::typeOptions()),
                TernaryFilter::make('completed_at')
                    ->label(__('Completion'))
                    ->nullable()
                    ->trueLabel(__('Completed'))
                    ->falseLabel(__('Open')),
                Filter::make('overdue')
                    ->label(__('Overdue'))
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNull('completed_at')
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now())),
                Filter::make('due_today')
                    ->label(__('Due today'))
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNull('completed_at')
                        ->whereDate('due_at', today())),
                Filter::make('mine')
                    ->label(__('Mine'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('user_id', auth()->id())),
            ])
            ->recordActions(TableRecordActions::viewEditDelete())
            ->toolbarActions([
                BulkAction::make('mark_complete')
                    ->label(__('Mark complete'))
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $records->each(fn (Activity $activity) => $activity->update([
                            'completed_at' => now(),
                        ]));
                    })
                    ->deselectRecordsAfterCompletion(),
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Details'))
                    ->schema([
                        TextEntry::make('type')
                            ->label(__('Type'))
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => static::typeOptions()[$state] ?? $state),
                        TextEntry::make('subject')->label(__('Subject')),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('due_at')
                            ->label(__('Due at'))
                            ->dateTime(config('app.datetime_format'))
                            ->placeholder('—'),
                        TextEntry::make('completed_at')
                            ->label(__('Completed at'))
                            ->dateTime(config('app.datetime_format'))
                            ->placeholder('—'),
                        TextEntry::make('lead.title')
                            ->label(__('Lead'))
                            ->placeholder('—')
                            ->url(fn (Activity $record): ?string => $record->lead_id
                                ? LeadResource::getUrl('view', ['record' => $record->lead_id])
                                : null)
                            ->color('primary'),
                        TextEntry::make('contact.full_name')
                            ->label(__('Contact'))
                            ->placeholder('—')
                            ->url(fn (Activity $record): ?string => $record->contact_id
                                ? ContactResource::getUrl('view', ['record' => $record->contact_id])
                                : null)
                            ->color('primary'),
                        TextEntry::make('user.name')
                            ->label(__('Logged by'))
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label(__('Created'))
                            ->dateTime(config('app.datetime_format')),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'view' => ViewActivity::route('/{record}'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', Filament::getTenant()->id)
            ->with(['lead', 'contact', 'user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['subject', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Activity $record */
        $details = [__('Type') => static::typeOptions()[$record->type] ?? $record->type];
        if ($record->due_at) {
            $details[__('Due')] = $record->due_at->format(config('app.datetime_format'));
        }

        return $details;
    }
}

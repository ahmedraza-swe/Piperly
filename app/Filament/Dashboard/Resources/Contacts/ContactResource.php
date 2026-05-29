<?php

namespace App\Filament\Dashboard\Resources\Contacts;

use App\Filament\Dashboard\Resources\Contacts\Pages\CreateContact;
use App\Filament\Dashboard\Resources\Contacts\Pages\EditContact;
use App\Filament\Dashboard\Resources\Contacts\Pages\ListContacts;
use App\Filament\Dashboard\Resources\Contacts\Pages\ViewContact;
use App\Filament\Dashboard\Resources\Leads\LeadResource;
use App\Filament\Dashboard\Support\TableRecordActions;
use App\Models\Contact;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 0;

    public static function getNavigationGroup(): ?string
    {
        return __('Contacts');
    }

    public static function getNavigationLabel(): string
    {
        return __('Contacts');
    }

    public static function getModelLabel(): string
    {
        return __('Contact');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Contacts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Contact'))
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('First name'))
                            ->required()
                            ->maxLength(120),
                        TextInput::make('last_name')
                            ->label(__('Last name'))
                            ->maxLength(120),
                        TextInput::make('job_title')
                            ->label(__('Job title'))
                            ->maxLength(120),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(50),
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
                        Toggle::make('is_primary')
                            ->label(__('Primary contact for this lead'))
                            ->helperText(__('Only one primary per lead; saving clears others on the same lead.'))
                            ->default(false),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('lead.title')
                    ->label(__('Lead'))
                    ->placeholder('—')
                    ->url(fn (Contact $record): ?string => $record->lead_id
                        ? LeadResource::getUrl('view', ['record' => $record->lead_id])
                        : null)
                    ->color('primary')
                    ->toggleable(),
                IconColumn::make('is_primary')
                    ->label(__('Primary'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('lead_id')
                    ->label(__('Lead'))
                    ->relationship(
                        name: 'lead',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('tenant_id', Filament::getTenant()->id)
                            ->orderBy('title'),
                    )
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_primary')
                    ->label(__('Primary contact')),
                Filter::make('standalone')
                    ->label(__('Standalone (no lead)'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('lead_id')),
            ])
            ->recordActions(TableRecordActions::viewEditDelete())
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Profile'))
                    ->schema([
                        TextEntry::make('full_name')->label(__('Name')),
                        TextEntry::make('job_title')->label(__('Job title'))->placeholder('—'),
                        TextEntry::make('email')->label(__('Email'))->placeholder('—')->copyable(),
                        TextEntry::make('phone')->label(__('Phone'))->placeholder('—'),
                        TextEntry::make('is_primary')
                            ->label(__('Primary for lead'))
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? __('Yes') : __('No'))
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('lead.title')
                            ->label(__('Linked lead'))
                            ->placeholder('—')
                            ->url(fn (Contact $record): ?string => $record->lead_id
                                ? LeadResource::getUrl('view', ['record' => $record->lead_id])
                                : null)
                            ->color('primary'),
                        TextEntry::make('updated_at')
                            ->label(__('Updated'))
                            ->dateTime(config('app.datetime_format')),
                    ])->columns(2),
                Section::make(__('Activity on this contact'))
                    ->schema([
                        TextEntry::make('activities_block')
                            ->label('')
                            ->state(function (Contact $record): string {
                                $items = $record->activities()
                                    ->with('user')
                                    ->limit(12)
                                    ->get()
                                    ->map(function ($activity): string {
                                        return sprintf(
                                            '[%s] %s — %s',
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
                            ->placeholder(__('No notes.')),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
            'create' => CreateContact::route('/create'),
            'view' => ViewContact::route('/{record}'),
            'edit' => EditContact::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', Filament::getTenant()->id)
            ->with(['lead']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Contact $record */
        $details = [];
        if ($record->email) {
            $details[__('Email')] = $record->email;
        }
        if ($record->lead) {
            $details[__('Lead')] = $record->lead->title;
        }

        return $details;
    }
}

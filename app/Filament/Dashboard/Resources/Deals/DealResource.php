<?php

namespace App\Filament\Dashboard\Resources\Deals;

use App\Filament\Dashboard\Resources\Deals\Pages\CreateDeal;
use App\Filament\Dashboard\Resources\Deals\Pages\DealKanban;
use App\Filament\Dashboard\Resources\Deals\Pages\EditDeal;
use App\Filament\Dashboard\Resources\Deals\Pages\ListDeals;
use App\Filament\Dashboard\Resources\Deals\Pages\ViewDeal;
use App\Filament\Dashboard\Support\TableRecordActions;
use App\Models\CrmPipelineStage;
use App\Models\Deal;
use App\Support\LocaleMoney;
use App\Services\CrmPipelineService;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    public static function getNavigationGroup(): ?string
    {
        return __('Pipeline');
    }

    public static function getNavigationLabel(): string
    {
        return __('Deals');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Deal Information'))
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('company_name')
                        ->label(__('Company'))
                        ->maxLength(255),
                    Select::make('pipeline_stage_id')
                        ->label(__('Pipeline Stage'))
                        ->required()
                        ->options(function () {
                            $tenant = Filament::getTenant();
                            app(CrmPipelineService::class)->ensureDefaultStages($tenant);

                            return CrmPipelineStage::query()
                                ->where('tenant_id', $tenant->id)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id');
                        }),
                    Select::make('status')
                        ->required()
                        ->default('open')
                        ->options([
                            'open' => __('Open'),
                            'won' => __('Won'),
                            'lost' => __('Lost'),
                        ]),
                    Select::make('owner_user_id')
                        ->label(__('Owner'))
                        ->options(fn () => Filament::getTenant()->users()
                            ->select('users.name', 'users.id')
                            ->pluck('users.name', 'users.id')),
                    TextInput::make('value')
                        ->numeric()
                        ->prefix('$'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('company_name')->label(__('Company'))->toggleable(),
                TextColumn::make('pipelineStage.name')
                    ->label(__('Stage'))
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'primary',
                    }),
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state): ?string => LocaleMoney::currency($state !== null ? (float) $state : null))
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->label(__('Owner'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('pipeline_stage_id')
                    ->label(__('Stage'))
                    ->options(function () {
                        $tenant = Filament::getTenant();
                        app(CrmPipelineService::class)->ensureDefaultStages($tenant);

                        return CrmPipelineStage::query()
                            ->where('tenant_id', $tenant->id)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id');
                    }),
                SelectFilter::make('status')
                    ->options([
                        'open' => __('Open'),
                        'won' => __('Won'),
                        'lost' => __('Lost'),
                    ]),
                SelectFilter::make('owner_user_id')
                    ->label(__('Owner'))
                    ->options(fn () => Filament::getTenant()->users()
                        ->select('users.name', 'users.id')
                        ->pluck('users.name', 'users.id')),
            ])
            ->recordActions(TableRecordActions::viewEditDelete(
                __('View deal'),
                __('Edit deal'),
                __('Delete deal'),
            ))
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeals::route('/'),
            'kanban' => DealKanban::route('/kanban'),
            'create' => CreateDeal::route('/create'),
            'view' => ViewDeal::route('/{record}'),
            'edit' => EditDeal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', Filament::getTenant()->id);
    }
}

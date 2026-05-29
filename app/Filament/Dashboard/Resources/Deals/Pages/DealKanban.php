<?php

namespace App\Filament\Dashboard\Resources\Deals\Pages;

use App\Filament\Dashboard\Resources\Deals\DealResource;
use App\Models\CrmPipelineStage;
use App\Models\Deal;
use App\Services\CrmPipelineService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class DealKanban extends Page
{
    protected static string $resource = DealResource::class;

    protected string $view = 'filament.dashboard.resources.deals.pages.deal-kanban';

    public array $columns = [];

    public function mount(): void
    {
        $this->loadBoard();
    }

    public function getHeading(): string
    {
        return __('Deal pipeline');
    }

    public function getSubheading(): ?string
    {
        return __('Drag cards between stages. Open deals only.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('table')
                ->label(__('Table view'))
                ->url(DealResource::getUrl('index'))
                ->color('gray'),
            Action::make('create')
                ->label(__('Add deal'))
                ->url(DealResource::getUrl('create'))
                ->color('primary'),
        ];
    }

    public function loadBoard(): void
    {
        $tenant = Filament::getTenant();
        $stages = app(CrmPipelineService::class)->ensureDefaultStages($tenant);

        $deals = Deal::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'open')
            ->with('owner')
            ->orderByDesc('updated_at')
            ->get();

        $nullDeals = $deals->whereNull('pipeline_stage_id')->values();

        $columns = [];

        if ($nullDeals->isNotEmpty()) {
            $columns[] = [
                'id' => null,
                'name' => __('Unassigned'),
                'deals' => $nullDeals->map(fn (Deal $d) => $this->dealToArray($d))->all(),
            ];
        }

        foreach ($stages as $stage) {
            $stageDeals = $deals->where('pipeline_stage_id', $stage->id)->values();
            $columns[] = [
                'id' => $stage->id,
                'name' => $stage->name,
                'deals' => $stageDeals->map(fn (Deal $d) => $this->dealToArray($d))->all(),
            ];
        }

        $this->columns = $columns;
    }

    /**
     * @return array{id: int, title: string, company: ?string, value: ?string, owner: ?string}
     */
    protected function dealToArray(Deal $deal): array
    {
        return [
            'id' => $deal->id,
            'title' => $deal->title,
            'company' => $deal->company_name,
            'value' => $deal->value !== null
                ? number_format((float) $deal->value, 2)
                : null,
            'owner' => $deal->owner?->name,
        ];
    }

    public function moveDeal(int $dealId, ?int $stageId): void
    {
        $tenant = Filament::getTenant();

        $deal = Deal::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'open')
            ->findOrFail($dealId);

        if ($stageId !== null) {
            $exists = CrmPipelineStage::query()
                ->where('tenant_id', $tenant->id)
                ->whereKey($stageId)
                ->exists();

            if (! $exists) {
                Notification::make()
                    ->title(__('Invalid stage'))
                    ->danger()
                    ->send();

                return;
            }
        }

        $deal->update([
            'pipeline_stage_id' => $stageId,
        ]);

        $this->loadBoard();

        Notification::make()
            ->title(__('Stage updated'))
            ->success()
            ->send();
    }
}

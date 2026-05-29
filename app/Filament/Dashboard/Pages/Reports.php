<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Widgets\ReportsDealOutcomeTrendWidget;
use App\Filament\Dashboard\Widgets\ReportsOverviewWidget;
use App\Filament\Dashboard\Widgets\ReportsTopOwnersWidget;
use App\Models\Deal;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Page
{
    protected string $view = 'filament.dashboard.pages.reports';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('Insights & Forecast');
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ReportsOverviewWidget::class,
            ReportsDealOutcomeTrendWidget::class,
            ReportsTopOwnersWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportLeads')
                ->label(__('Export leads CSV'))
                ->icon('heroicon-m-arrow-down-tray')
                ->action(fn (): StreamedResponse => $this->exportLeadsCsv()),
            Action::make('exportDeals')
                ->label(__('Export deals CSV'))
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->exportDealsCsv()),
        ];
    }

    protected function exportLeadsCsv(): StreamedResponse
    {
        $tenantId = Filament::getTenant()->id;
        $rows = Lead::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'company_name', 'status', 'source', 'value', 'created_at']);

        $headers = ['ID', 'Title', 'Company', 'Status', 'Source', 'Value', 'Created At'];
        $filename = 'leads-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows, $headers): void {
            $file = fopen('php://output', 'wb');
            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->title,
                    $row->company_name,
                    $row->status,
                    $row->source,
                    (string) ($row->value ?? 0),
                    optional($row->created_at)->toDateTimeString(),
                ]);
            }

            fclose($file);
        }, $filename);
    }

    protected function exportDealsCsv(): StreamedResponse
    {
        $tenantId = Filament::getTenant()->id;
        $rows = Deal::query()
            ->where('tenant_id', $tenantId)
            ->with(['pipelineStage:id,name', 'owner:id,name'])
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'company_name', 'status', 'value', 'pipeline_stage_id', 'owner_user_id', 'created_at']);

        $headers = ['ID', 'Title', 'Company', 'Status', 'Value', 'Stage', 'Owner', 'Created At'];
        $filename = 'deals-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows, $headers): void {
            $file = fopen('php://output', 'wb');
            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->title,
                    $row->company_name,
                    $row->status,
                    (string) ($row->value ?? 0),
                    $row->pipelineStage?->name,
                    $row->owner?->name,
                    optional($row->created_at)->toDateTimeString(),
                ]);
            }

            fclose($file);
        }, $filename);
    }
}

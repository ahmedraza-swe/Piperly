<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Dashboard;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

/**
 * Tenant home: 12-column widget grid with consistent gaps so KPIs, charts,
 * and workspace blocks align instead of a loose 2-column layout.
 */
class TenantDashboard extends Dashboard
{
    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int | array
    {
        return 12;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...(method_exists($this, 'getFiltersForm') ? [$this->getFiltersFormContentComponent()] : []),
                Grid::make($this->getColumns())
                    ->extraAttributes([
                        'class' => 'fi-tenant-crm-dashboard-grid',
                    ])
                    ->schema($this->getWidgetsSchemaComponents($this->getWidgets()))
                    ->gap(true),
            ]);
    }
}

<?php

namespace App\Filament\Dashboard\Support;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

/**
 * Standard icon-only table row actions for the tenant dashboard.
 * Tooltips carry the human-readable label (accessibility + less visual noise).
 *
 * Use these helpers on new resources instead of ->button() on View/Edit/Delete.
 */
final class TableRecordActions
{
    /**
     * @return array<int, ViewAction|EditAction|DeleteAction>
     */
    public static function viewEditDelete(
        ?string $viewTooltip = null,
        ?string $editTooltip = null,
        ?string $deleteTooltip = null,
    ): array {
        return [
            ViewAction::make()
                ->iconButton()
                ->tooltip($viewTooltip ?? __('View')),
            EditAction::make()
                ->iconButton()
                ->tooltip($editTooltip ?? __('Edit')),
            DeleteAction::make()
                ->iconButton()
                ->tooltip($deleteTooltip ?? __('Delete')),
        ];
    }

    /**
     * @return array<int, ViewAction>
     */
    public static function viewOnly(?string $viewTooltip = null): array
    {
        return [
            ViewAction::make()
                ->iconButton()
                ->tooltip($viewTooltip ?? __('View')),
        ];
    }

    /**
     * @return array<int, EditAction>
     */
    public static function editOnly(?string $editTooltip = null): array
    {
        return [
            EditAction::make()
                ->iconButton()
                ->tooltip($editTooltip ?? __('Edit')),
        ];
    }

    /**
     * @return array<int, DeleteAction>
     */
    public static function deleteOnly(?string $deleteTooltip = null): array
    {
        return [
            DeleteAction::make()
                ->iconButton()
                ->tooltip($deleteTooltip ?? __('Delete')),
        ];
    }
}

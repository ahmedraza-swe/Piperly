<?php

namespace App\Support;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;

/**
 * Consistent Filament database (bell) notifications for the tenant CRM.
 */
final class CrmBell
{
    /**
     * Primary line + optional supporting detail (blank line for visual separation in the modal).
     */
    public static function twoLineBody(string $primary, ?string $secondary = null): string
    {
        $secondary = $secondary !== null && $secondary !== '' ? trim($secondary) : null;

        return $secondary === null ? $primary : $primary."\n\n".$secondary;
    }

    /**
     * @param  'success'|'warning'|'danger'|'info'|'gray'  $tone
     * @param  array<int, Action>  $actions
     */
    public static function for(
        string $title,
        string $body,
        string $tone,
        ?string $icon = null,
        array $actions = [],
    ): Notification {
        $n = Notification::make()
            ->title($title)
            ->body($body)
            ->persistent()
            ->iconSize(IconSize::Large);

        $n = match ($tone) {
            'success' => $n->success()->color('success')->iconColor('success'),
            'warning' => $n->warning()->color('warning')->iconColor('warning'),
            'danger' => $n->danger()->color('danger')->iconColor('danger'),
            'info' => $n->info()->color('info')->iconColor('info'),
            default => $n->color('gray')->iconColor('gray'),
        };

        if ($icon !== null) {
            $n->icon($icon);
        }

        if ($actions !== []) {
            $n->actions($actions);
        }

        return $n;
    }

    public static function openAction(string $url, ?string $label = null, string $color = 'primary'): Action
    {
        return Action::make('open')
            ->label($label ?? __('Open'))
            ->button()
            ->color($color)
            ->url($url, shouldOpenInNewTab: true);
    }

    public static function secondaryButton(string $name, string $url, string $label): Action
    {
        return Action::make($name)
            ->label($label)
            ->button()
            ->color('gray')
            ->url($url, shouldOpenInNewTab: true);
    }
}

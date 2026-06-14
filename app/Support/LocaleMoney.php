<?php

namespace App\Support;

use Illuminate\Support\Number;

/**
 * Filament / Laravel use {@see Number::currency()} which requires the intl extension.
 * Laragon and some hosts ship without intl enabled; this helper falls back safely.
 */
final class LocaleMoney
{
    public static function currency(float|int|null $amount, string $currency = 'USD', ?string $locale = null): ?string
    {
        if ($amount === null) {
            return null;
        }

        if (extension_loaded('intl')) {
            return Number::currency((float) $amount, $currency, $locale);
        }

        $float = (float) $amount;

        return match (strtoupper($currency)) {
            'USD' => '$'.number_format($float, 2),
            default => number_format($float, 2).' '.strtoupper($currency),
        };
    }

    /** Plan prices are stored in minor units (cents). */
    public static function formatMinorUnits(int|float|null $amount, string $currency = 'USD'): string
    {
        if ($amount === null) {
            return '';
        }

        return self::currency(((float) $amount) / 100, $currency) ?? '';
    }
}

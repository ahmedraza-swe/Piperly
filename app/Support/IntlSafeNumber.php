<?php

namespace App\Support;

use Illuminate\Support\Number;

/**
 * Laravel {@see Number::format()} requires ext-intl. Filament pagination and tables use it;
 * this helper keeps the UI working when intl is disabled (per-project, no global php.ini change).
 */
final class IntlSafeNumber
{
    public static function format(int|float|string|null $number, ?string $locale = null): string
    {
        if ($number === null || $number === '') {
            return '0';
        }

        $n = is_numeric($number) ? (float) $number : 0.0;

        if (extension_loaded('intl')) {
            return (string) Number::format($n, locale: $locale ?? app()->getLocale());
        }

        if (floor($n) === $n && abs($n) < 1e15) {
            return number_format((int) $n, 0, '.', ',');
        }

        return number_format($n, 2, '.', ',');
    }
}

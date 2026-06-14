# PHP runtime notes (this project)

## `ext-intl` not loaded (Laragon / shared PHP)

Laravel `Illuminate\Support\Number::format()` and `Number::currency()` require the **intl** extension. Filament uses these in:

- Table pagination overview and page labels (`filament::components.pagination.*`)
- “Select all” selection counts (`filament-tables::index`)

If `intl` is missing, those calls throw `RuntimeException` and Filament pages (for example **Leads**) return **500**.

### What this repo does (no global `php.ini` change)

- **`App\Support\IntlSafeNumber`** — safe integer-style formatting when `intl` is off.
- **`App\Support\LocaleMoney`** — currency display for CRM columns without `Number::currency()` when `intl` is off.
- **`@money` Blade directive** — overridden in `AppServiceProvider` to use `LocaleMoney::formatMinorUnits()` (checkout, pricing, trial flow).
- **Published Blade overrides** (take precedence over vendor only for this app):
  - `resources/views/vendor/filament/components/pagination/index.blade.php`
  - `resources/views/vendor/filament/components/pagination/item.blade.php`
  - `resources/views/vendor/filament-tables/index.blade.php` (selection indicator count)

On boot, if `intl` is missing, **`AppServiceProvider`** logs **one warning per PHP worker** to the default log channel with a pointer to this file.

### Optional: enable intl for this stack only

If you use a **dedicated** PHP binary or `php.ini` for this Laragon site, you can uncomment `extension=intl` there. That does not change other projects that use a different PHP path.

### After upgrading Filament

Re-diff the vendor copies above against `vendor/filament/...` and re-apply the `IntlSafeNumber` / `LocaleMoney` substitutions if Filament changes those templates.

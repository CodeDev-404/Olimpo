# OLIMPO Web — Agent Guide

Laravel 13 + Livewire 3 + Tailwind v3. Spanish schema, internal HR app. Peru timezone.

## Environment

- PHP 8.5 via `C:\php\php.exe` (Windows dev); Railway uses platform PHP + `railpack.json`
- SQLite dev (`database/database.sqlite`), PostgreSQL on Railway
- All drivers use `database` (cache, session, queue) — NOT file/redis
- `America/Lima` for all `now()` calls
- `.env` has `CONSULTADNI_API_KEY` + `KMENTE_API_TOKEN` for DNI lookups

## Commands

```powershell
# Dev (4 concurrent: serve, queue, logs, vite)
composer dev

# Dev server only (no vite/queue)
.\start.ps1

# Build frontend
npm run build

# Tests (clears config first)
composer test

# Syntax-check a PHP file
php -l path/to/file.php

# Format PHP
C:\php\php.exe vendor\bin\pint
```

## Architecture

- **Layout chain**: `->layout('layouts.olimpo')` in every Livewire component's `render()`, which delegates to `components/layouts/app.blade.php`.
- **Panels** defined in `config/olimpo.php` (array key → route name, slug, title, icon, group). All routes in `routes/web.php` under `/olimpo/*` (`auth`, `verified` middleware).
- **Layout**: `app.blade.php` — sidebar `@persist('sidebar')`, header `@persist('header')`, all sidebar `<a>` links have `wire:navigate`.
- **Dark mode**: `class` strategy, toggled by `localStorage.getItem('dark')` in `<head>` inline script.
- **Custom CSS**: `resources/css/app.css` has card/btn/input-field/badge/modal/toast/calendar/table-adminlte classes.
- **`t(string)` helper** (`app/helpers.php`): `mb_convert_case(mb_strtolower(...), MB_CASE_TITLE)` for Spanish title case.
- **Lucide icons**: `<i data-lucide="icon-name"></i>` + `lucide.createIcons()` init in app.js.
- **Scheduler**: `routes/console.php` — daily `cumpleanos-recordatorio` at 07:30.
- **External APIs**: `DniConsultaService` (json.pe) + K-Mente for DNI lookups.
- **Exports**: Maatwebsite/Laravel Excel at `app/Exports/`, `app/Imports/`.

## Conventions & Gotchas

- **Spanish schema**: all table/column names in Spanish; dates stored as `d/m/Y` strings. Always use `Carbon::createFromFormat('d/m/Y', $val)`, never `Carbon::parse()`.
- **Models**: `User` uses PHP 8 `#[Fillable]` / `#[Hidden]` attributes; all others use legacy `$fillable` arrays.
- **Tailwind**: custom `ink` palette (`ink-50`–`950`) + `scale(hex)` function generates full shade scales for `primary/#5D87FF`, `violet`, `ember`, etc. Don't use Tailwind default `purple-*` — use `violet-*`.
- **Fonts**: Poppins (`font-display`) for headings, DM Sans for body/labels.
- **`x-cloak`** must be on root `x-data` divs to prevent Alpine flash.
- **Table sticky headers** use `z-[1]` (not `z-10`) to avoid overlapping dropdowns.
- **Filter cards** need `style="overflow:visible"` for calendar/dropdown clipping.

## Important Constraints

- **`max_input_vars=1000`** (`php.ini`) — Livewire snapshots with >1000 serialized props cause silent 404. Fix: move large data arrays to `#[Computed]`. Already applied to `Cumpleanos`, `Asistencia`, `Personal`.
- **`php artisan serve`** strips `Content-Encoding: gzip` at the protocol level. The `PerformanceHeaders` middleware (`app/Http/Middleware/PerformanceHeaders.php`) compresses HTML via `gzencode()` but the header won't appear in dev. Works in production (Apache/Nginx).
- **CSRF exception**: `livewire/*` is excluded in `bootstrap/app.php` — required for Livewire POST updates.
- **Kaspersky antivirus** adds ~5s to every page load on Windows dev (intercepts via `gc.kis.v2.scr.kaspersky-labs.com`).

## Livewire Patterns

### Snapshot Optimization (`#[Computed]`)

Large public properties serialized into Livewire snapshots cause 404s or slow responses. Applied pattern:
```php
use Livewire\Attributes\Computed;

#[Computed]
public function getPersonalProperty()
{
    return Personal::query()->...->get();
}
```
Blade: change `$personal` → `$this->personal`. Affected: `Asistencia`, `Personal`, `Cumpleanos`.

### `wire:navigate` + `@persist`

- Sidebar links use `wire:navigate` for SPA-like navigation (Livewire morphs only changed DOM).
- `@persist('sidebar')` and `@persist('header')` in `app.blade.php` prevent sidebar/header re-render on navigation.
- The old JS handler (`window.location.href = link.href`) that intercepted sidebar clicks has been **removed** — it broke `wire:navigate`.

### Cache Patterns

- Dashboard data methods use `cache()->remember()` with short TTLs (60s–300s).
- ControlVehiculos autocomplete queries use `cache()->remember(..., 300)` with instance cache (`_bmaAuto`/`_combAuto`) to avoid redundant calls.
- Cumpleaños list cached 3600s under `cumpleanos_list` key.
- Cache store is `database` (not file/redis).

## Filter Pattern (Alpine + Livewire)

- Custom dropdowns use `$watch('sel', v => $wire.set('prop', v))` for **real-time** sync (NOT `@entangle`).
- `@filter-reset.window` handler must check `$event.detail.reset` to reset `sel` — `$wire.method()` doesn't update client `$wire.*` before event fires.
```blade
<div x-data="{ open: false, sel: '{{ $prop }}', opts: [...] }"
     x-init="$watch('sel', v => $wire.set('prop', v))"
     @filter-reset.window="if($event.detail.reset){sel=''}else{var f=$wire.prop||'';if(sel!==f)sel=f}">
```

## Table Pagination — Nested Component

- Child component (e.g. `OcurrenciasTable`) handles pagination independently so parent doesn't re-render on page change.
- Parent passes filters + `:wire:key` with hash of all filter values — forces child re-creation when filters change.
- Pagination (`previousPage`, `nextPage`, `gotoPage`) is scoped inside child.

## Calendar Widget

- Sunday-first headers: `Do Lu Ma Mi Ju Vi Sá` (Spanish); `dayOfWeek` is Sunday-based.
- Today highlight: `.cal-day.today` with ember/violet inset shadow.
- `isSel(d)` compares `fmt(d)` against `val` or `fmt(new Date())` when `val` empty.

## Deployment (Railway)

1. Build: `composer install --no-dev && npm ci && npm run build`
2. Start: `php artisan serve --port=$PORT`
3. Env vars from `.env.production`
4. PHP extensions: gd, pgsql, pdo_pgsql (via `railpack.json`)

# OLIMPO Web — Agent Guide

Laravel 13 + Livewire 3 (full-stack) + Volt 1.7 (functional auth) + Tailwind v3.

## Environment

- PHP: `C:\php\php.exe` (Windows dev; Railway uses platform PHP)
- Composer: `C:\php\php.exe C:\php\composer.phar <cmd>`
- Artisan: `C:\php\php.exe artisan <cmd>`
- Database: SQLite dev (`database/database.sqlite`), PostgreSQL on Railway
- Cache/Session/Queue all use `database` driver in dev

## Commands

```powershell
# Dev server (4 concurrent processes: serve, queue, logs, vite)
composer dev

# Build frontend (Vite)
npm run build

# Tests (clears config first)
composer test

# Format PHP (Laravel Pint)
C:\php\php.exe vendor\bin\pint

# Seed with demo data
C:\php\php.exe artisan migrate --seed
```

Default credentials: `admin@olimpo.com` / `admin123` (role: admin), `user@olimpo.com` / `user123` (role: user).

## Architecture

- **Entrypoint**: `routes/web.php` — all app routes under `/olimpo/*` (auth+verified middleware)
- **Auth routes**: `routes/auth.php` — Volt functional pages (login, register, password reset)
- **Components**: `app/Livewire/Olimpo/*` (14 components), views at `resources/views/livewire/olimpo/`
- **Models** in `app/Models/`: `Personal`, `Ocurrencia`, `Asistencia`, `Cumpleano`, `Camioneta`, `Cargo`, `TipoOcurrencia`, `User`
- **Exports/Imports**: Maatwebsite/Laravel Excel — `app/Exports/*`, `app/Imports/*`
- **External API**: `DniConsultaService` via json.pe (needs `JSONPE_TOKEN` in .env)
- **Scheduler**: `routes/console.php` — daily birthday reminders at 07:30 (`cumpleanos-recordatorio`)

## Conventions

- Schema: Spanish table/column names; dates stored as `d/m/Y` strings
- Models use `#[Fillable]` / `#[Hidden]` PHP 8 attributes (User), or legacy `$fillable` array (others)
- Tailwind dark mode via `class` strategy; Figtree font; `@tailwindcss/forms` plugin
- No CI workflows; no Pint config file (uses package defaults)

## Deployment (Railway)

1. Build: `composer install --no-dev && npm ci && npm run build`
2. Start: `php artisan serve --port=$PORT`
3. Set env vars from `.env.production`

# Fortify Backend Command

## Command

```bash
vendor/bin/laraprep fortify:backend
```

This command sets up Laravel Fortify as a backend-only authentication layer.

## What it does

In apply mode, Laraprep currently:

1. Detects the Laravel project root and version.
2. Installs `laravel/fortify` if it is missing.
3. Runs `php artisan fortify:install` when Fortify has not been published yet.
4. Configures `config/fortify.php` for backend-only use.
5. Updates `app/Models/User.php` to implement `MustVerifyEmail` when email verification is enabled.
6. Ensures `App\Providers\FortifyServiceProvider::class` is registered in `bootstrap/providers.php`.
7. Runs migrations unless `--no-migrate` is passed.
8. Runs `php artisan optimize:clear`.

## Default behavior

Dry-run is the default. Without `--apply`, Laraprep prints planned actions and does not modify files.

Default feature intent:

- registration enabled
- password reset enabled
- email verification enabled
- two-factor disabled
- passkeys disabled

## Backend-only behavior

The command is designed to avoid frontend scaffolding. Laraprep does not create:

- Blade auth views
- Livewire auth components
- Inertia pages
- Vue components
- React components
- Svelte components

Fortify still provides backend auth routes. Confirm the exact final route set in the target app with:

```bash
php artisan route:list
```

## Files it may change

- `composer.json`
- `composer.lock`
- `config/fortify.php`
- `app/Models/User.php`
- `bootstrap/providers.php`
- `routes/web.php` when `--test-route` is used

Fortify itself may also publish additional files such as its service provider, actions, and migrations.

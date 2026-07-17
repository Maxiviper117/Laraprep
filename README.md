# Laraprep

Laraprep is a Composer-installed CLI for safe, repeatable Laravel backend setup tasks.

The MVP command is `vendor/bin/laraprep fortify:backend`. It installs and configures Laravel Fortify for backend-only authentication, defaults to dry-run mode, creates backups before file changes, and avoids generating frontend auth views.

## Requirements

- PHP 8.3+
- Composer 2
- A Laravel 13 project

## Installation

```bash
composer require maxiviper117/laraprep --dev
```

## Usage

Dry-run is the default:

```bash
vendor/bin/laraprep fortify:backend
```

Apply the plan:

```bash
vendor/bin/laraprep fortify:backend --apply
```

Example with explicit options:

```bash
vendor/bin/laraprep fortify:backend --apply --two-factor --test-route --no-migrate
```

## Options

- `--registration` / `--no-registration`
- `--reset-passwords` / `--no-reset-passwords`
- `--verify-email` / `--no-verify-email`
- `--two-factor`
- `--passkeys`
- `--no-migrate`
- `--no-backup`
- `--force`
- `--test-route`
- `--apply`

## Safety behavior

- Dry-run by default
- Backs up existing files before Laraprep edits them unless `--no-backup` is used
- Never overwrites an existing backup; collisions become timestamped backups
- Uses AST edits for `app/Models/User.php` and `bootstrap/providers.php`
- Stops on the first apply failure and reports partial completion
- Supports Laravel 13 only in the MVP

## Expected backend endpoints

- `POST /login`
- `POST /logout`
- `POST /register` when registration is enabled
- `POST /forgot-password` and `POST /reset-password` when password resets are enabled
- `POST /email/verification-notification` and `GET /email/verify/{id}/{hash}` when email verification is enabled

Confirm the actual route set with `php artisan route:list` after apply.

## Development

```bash
composer install
composer check
pnpm install
pnpm docs:build
```

### Local workbench

For real end-to-end validation against a disposable Laravel 13 app, use the local gitignored workbench:

```powershell
./scripts/setup-workbench.ps1
./scripts/run-workbench-fortify.ps1
./scripts/run-workbench-fortify.ps1 -Apply
```

Reset it with:

```powershell
./scripts/reset-workbench.ps1
```

The setup script creates `workbench/laravel13-app` using `composer create-project laravel/laravel "^13.0"` and installs this package into that app via a local Composer path repository.

## License

MIT. See [LICENSE.md](LICENSE.md).

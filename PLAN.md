# Laraprep MVP Implementation

## Summary

Build `maxiviper117/laraprep` as a PHP 8.3+ Composer CLI package supporting Laravel 13. The initial command is `vendor/bin/laraprep fortify:backend`, dry-run by default, backend-only, idempotent, and backup-aware.

## Implementation Changes

- Replace template identity with `Maxiviper117\Laraprep`, add Symfony Console/Process and PHP Parser dependencies, register `bin/laraprep`, and align CI with PHP 8.3+.
- Implement Laravel project/version detection using `artisan`, `composer.json`, and installed Composer metadata. Reject non-Laravel, unsupported-version, ambiguous-model, and unparseable states.
- Implement a typed change-plan system with planned/applied/skipped/failed/manual-review results and deterministic console reporting.
- Implement process runners that capture exit codes and output. Apply stops on the first failure, preserves completed work and backups, and reports partial completion.
- Implement backups for every existing file potentially changed by Laraprep, Composer, or `fortify:install`. Never overwrite an existing backup; use timestamped names on collision.
- Implement AST edits for `User.php` and `bootstrap/providers.php`, preserving existing imports, interfaces, and provider entries. Use structured editing for `config/fortify.php` and marker blocks for the optional test route.
- Implement all documented options: registration, password reset, email verification, two-factor, passkeys, migration, backup, force, test route, and apply. Reject contradictory option pairs.
- Detect Fortify capabilities before enabling passkeys; unsupported passkeys become a clear manual-review failure rather than guessed configuration.
- In apply mode: install Fortify if absent, run its installer only when required, configure selected features, update the user model/provider, optionally add the test route, run migrations unless disabled, clear caches, and summarize actual backend endpoints.
- Keep `.env`, frontend files, auth views, interactive prompts, rollback, report generation, Sanctum, and API foundation outside this MVP.
- Replace template README/docs with installation, dry-run/apply examples, option reference, safety behavior, supported versions, endpoint flow, and development instructions.

## Public Interfaces

- Binary: `vendor/bin/laraprep`
- Command: `fortify:backend`
- Exit codes distinguish success, invalid usage, unsupported project, planning failure, and partial apply failure.
- `--force` bypasses recognized customization warnings only; it never bypasses project/version validation or PHP parse failures.
- Migrations run by default under `--apply`; `--no-migrate` disables them.

## Test Plan

- Unit-test project/version detection, plan statuses, backup collision naming, marker blocks, process failures, feature option resolution, AST imports/interfaces/providers, and duplicate detection.
- Fixture-test fresh Laravel 13 projects, existing Fortify installations, customized configuration, existing interfaces/providers, missing or ambiguous user models, and broken PHP.
- Integration-test dry-run immutability, successful apply, repeated apply idempotency, `--no-backup`, `--no-migrate`, partial command failure, and all feature combinations.
- Snapshot concise dry-run, success, skipped, manual-review, and partial-failure output.
- Run Pint, PHPStan at maximum level, Rector dry-run, Pest, and documentation build.

## Assumptions

- Composer package identity is `maxiviper117/laraprep`; PHP namespace is `Maxiviper117\Laraprep`.
- Laravel 13 is the only supported application version initially.
- Email verification and migrations are enabled by default.
- CLI operation remains non-interactive and Composer-binary-only.
- Existing changes are preserved after failure because Composer operations and migrations cannot be reliably rolled back.

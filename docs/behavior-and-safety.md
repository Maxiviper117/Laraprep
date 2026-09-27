# Behavior And Safety

## Dry-run first

Laraprep defaults to dry-run mode so the command can be inspected before anything changes.

## Backups

Before Laraprep edits an existing file, it creates a backup unless `--no-backup` is passed.

Default backup naming:

```text
path/to/File.php.laraprep.bak
```

If that backup name already exists, Laraprep creates a timestamped backup instead of overwriting it.

## Idempotency

Laraprep is intended to be safe to re-run. It skips work that has already been completed, such as:

- Fortify already being installed
- Fortify published files already existing
- `User` already implementing `MustVerifyEmail`
- `FortifyServiceProvider` already being registered
- an existing Laraprep test route marker block

## Failure behavior

Apply mode stops on the first blocking failure. Completed changes and backups are preserved.

That is intentional. Composer operations, published files, and migrations are not treated as automatically reversible.

## Code modification strategy

Current implementation strategy:

- AST edits for `app/Models/User.php`
- AST edits for `bootstrap/providers.php`
- AST-generated rewrite for `config/fortify.php`
- marker-block insertion for the optional test route

## Actual route ownership

Laraprep does not hand-write Fortify’s core auth routes. It installs and configures Fortify, and Fortify registers the routes inside the Laravel app.

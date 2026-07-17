# Workbench

Laraprep includes a local gitignored workbench flow for testing the package against a real Laravel 13 application.

## Purpose

This is the fastest way to validate:

- Composer installation behavior
- Artisan command execution
- real Fortify publishing
- migrations
- backups
- idempotent reruns
- actual route registration

## Setup

From the repository root:

```powershell
./scripts/setup-workbench.ps1
```

That script:

1. creates `workbench/laravel13-app`
2. installs a fresh Laravel 13 app with Composer
3. adds this repository as a local Composer path repository
4. requires `maxiviper117/laraprep` into that app

## Run the command

Dry-run:

```powershell
./scripts/run-workbench-fortify.ps1
```

Apply:

```powershell
./scripts/run-workbench-fortify.ps1 -Apply
```

## Reset

To remove the disposable app and recreate it cleanly:

```powershell
./scripts/reset-workbench.ps1
```

## Notes

- `workbench/` is gitignored.
- The workbench is for local validation only.
- Real CI-driven Laravel integration tests are still deferred.

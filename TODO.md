# TODO

## Real Laravel Validation

- Add a local gitignored `workbench/` Laravel 13 app for manual end-to-end testing of `vendor/bin/laraprep fortify:backend`.
- Use the workbench to verify dry-run immutability, apply behavior, idempotent re-runs, backup creation, migration handling, cache clearing, and route output against a real Laravel project.
- Add small helper scripts for creating, resetting, and exercising the local workbench during development.

## Deferred Integration Coverage

- Add integration tests that create a temporary Laravel 13 app during the test run instead of committing a full Laravel fixture into the repository.
- Install Laraprep into that temp app through a local path repository and verify dry-run, apply, and repeated apply behavior end to end.
- Assert real Composer and Artisan execution paths, Fortify installation, file edits, backup files, and idempotency.
- Keep this out of CI for now; implement after the local workbench flow is stable.

## MVP Hardening

- Validate `--force` behavior properly instead of accepting the flag without using it for recognized customization warnings.
- Improve detection of customized or ambiguous Laravel project states and convert risky cases into clear manual-review outcomes.
- Tighten Fortify passkey capability detection against real installed Fortify versions instead of relying on a simple source-string check.
- Verify that backup coverage includes every file that can be changed directly or indirectly by Laraprep, Composer, or `php artisan fortify:install`.
- Improve failure reporting for partial apply runs so command output, completed steps, and next manual actions are clearer.

## Test Coverage Gaps

- Add tests for command exit codes across success, invalid usage, unsupported project, planning failure, and partial apply failure cases.
- Add tests for `bootstrap/providers.php` parse failures and other manual-review branches.
- Add tests for conflicting imports, existing interfaces, and unusual `User.php` shapes beyond the happy path.
- Add tests for marker-block route insertion against real Laravel-style `routes/web.php` content.
- Add snapshot coverage for dry-run, success, skipped, manual-review, and partial-failure CLI output.

## CLI And Output

- Add a deterministic, user-friendly change reporter instead of rendering raw grouped result messages directly in the command.
- Revisit endpoint reporting so it can optionally reflect actual registered Fortify routes from `php artisan route:list` after apply.
- Make manual next steps more specific when features are disabled, migrations are skipped, or a step fails.
- Decide whether to add a machine-readable output mode later without complicating the MVP command surface now.

## Docs And Developer Experience

- Document the local workbench workflow once it exists.
- Add a contributor note describing how to test Laraprep safely against a disposable Laravel app.
- Reconcile README and docs wording with the exact current implementation details as the command behavior hardens.
- Decide whether backup files should be gitignored automatically or only documented.

## Post-MVP Product Scope

- Implement `sanctum:spa`.
- Implement `api:foundation`.
- Decide whether Laravel 12 support is worth adding after Laravel 13 behavior is stable.
- Decide whether the package should remain Composer-binary-only or also expose Artisan command integration later.
- Revisit rollback, interactive prompts, JSON output, and generated setup reports only after the Fortify backend path is proven stable.

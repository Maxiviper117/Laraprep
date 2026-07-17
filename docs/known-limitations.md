# Known Limitations

This page documents gaps in the current implementation so the docs stay aligned with reality.

## Scope limitations

- Only Laravel 13 is supported.
- Only the `fortify:backend` command is implemented.
- The package is still pre-v1.

## Behavior gaps

- `--force` is accepted, but the full warning-bypass behavior is not implemented yet.
- The dry-run planner can still over-report some already-completed work in a previously modified project, even when apply mode correctly skips it.
- Endpoint reporting is curated by Laraprep and is not yet a full reflection of every Fortify route that may be present in the target app.

## Output and formatting

- AST rewrites can reformat target PHP files.
- The Laravel 13 `User.php` shape with attribute-based metadata still needs more polish in how import insertion is rendered after rewriting.

## Test coverage gaps

- There is no automated temp-Laravel integration test suite in CI yet.
- Real end-to-end validation currently depends on the local workbench flow.

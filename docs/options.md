# Options Reference

## Command options

### `--apply`

Apply the planned changes instead of showing only a dry-run preview.

### `--registration` / `--no-registration`

Enable or disable Fortify registration support.

### `--reset-passwords` / `--no-reset-passwords`

Enable or disable password reset support.

### `--verify-email` / `--no-verify-email`

Enable or disable email verification support.

### `--two-factor`

Enable Fortify two-factor authentication support.

### `--passkeys`

Enable Fortify passkey support when the installed Fortify version supports it.

### `--no-migrate`

Skip `php artisan migrate` during apply.

### `--no-backup`

Skip creating `.laraprep*.bak` backups before Laraprep edits existing files.

### `--force`

Reserved for bypassing recognized customization warnings. The current implementation accepts the flag, but the warning-bypass behavior is not fully implemented yet.

### `--test-route`

Append an optional Laraprep-managed test route block to `routes/web.php`.

## Exit codes

- `0`: success
- `2`: invalid option usage
- `3`: unsupported or non-Laravel project
- `4`: planning failure during dry-run
- `5`: partial or failed apply

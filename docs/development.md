# Development

## Local setup

```bash
composer install
pnpm install
```

## Main checks

```bash
composer check
pnpm docs:build
```

`composer check` currently runs:

- Pint
- PHPStan
- Rector dry-run
- Pest

## Documentation model

The VitePress docs are intentionally organized around a single sidebar so every page stays discoverable without relying on top navigation.

## Recommended validation loop

1. Run unit and static checks with `composer check`.
2. Build the docs with `pnpm docs:build`.
3. Validate the command against the local Laravel 13 workbench.

## CI notes

Current workflows cover:

- Composer validation
- test matrix
- Pint
- PHPStan
- Rector
- docs build and Pages deployment
- release-please automation

Workflow action versions are pinned and can be refreshed with:

```bash
npx actions-up --dir .github --recursive -y
```

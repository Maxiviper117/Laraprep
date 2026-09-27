---
layout: home

hero:
  name: Laraprep
  text: Safe Laravel backend setup, without starter-kit frontend code
  tagline: Composer-installed CLI tooling for repeatable Laravel 13 backend setup, starting with Fortify backend auth.
  actions:
    - theme: brand
      text: Install Laraprep
      link: /installation
    - theme: alt
      text: Fortify Backend Command
      link: /fortify-backend
    - theme: alt
      text: View on GitHub
      link: https://github.com/maxiviper117/laraprep

features:
  - title: Dry-run First
    details: Laraprep previews planned Fortify backend changes by default and only modifies the target Laravel app when you pass `--apply`.
  - title: Backend-only Fortify
    details: The current MVP installs and configures Fortify for backend auth flows without generating Blade, Livewire, Inertia, Vue, React, or Svelte auth UI.
  - title: Safe File Changes
    details: Existing files are backed up before Laraprep edits them, and the package uses AST-based edits for the user model and provider registration paths.
  - title: Real Laravel Validation
    details: The repository includes a local gitignored workbench flow so the command can be exercised against a real Laravel 13 application during development.
---

## Current MVP

Laraprep is intentionally narrow right now. The package currently ships one implemented command:

```bash
vendor/bin/laraprep fortify:backend
```

That command is designed to:

- detect a Laravel 13 project
- install and configure `laravel/fortify`
- patch backend auth-related files safely
- run migrations unless disabled
- keep frontend scaffolding out of scope

## What exists today

- Laravel 13 support
- PHP 8.3+ support
- dry-run and apply modes
- backup-aware file editing
- real local workbench validation
- VitePress documentation for the implemented command surface

## What to read next

- [Installation](/installation)
- [Fortify Backend Command](/fortify-backend)
- [Options Reference](/options)
- [Behavior And Safety](/behavior-and-safety)
- [Workbench](/workbench)

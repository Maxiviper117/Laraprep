# Installation

## Requirements

- PHP 8.3 or newer
- Composer 2
- A Laravel 13 project

## Install Laraprep

```bash
composer require maxiviper117/laraprep --dev
```

Laraprep is currently intended to be run as a Composer-installed CLI:

```bash
vendor/bin/laraprep
```

## First run

Dry-run is the default:

```bash
vendor/bin/laraprep fortify:backend
```

Apply the plan explicitly:

```bash
vendor/bin/laraprep fortify:backend --apply
```

## Supported project type

Laraprep currently expects:

- an `artisan` file
- a `composer.json`
- Laravel 13 metadata that can be detected from Composer files

If the project is not recognized as Laravel 13, the command exits with an unsupported-project error instead of guessing.

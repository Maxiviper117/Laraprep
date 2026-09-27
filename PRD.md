# PRD: Laraprep

## 1. Product Summary

Laraprep is a developer tool for running safe, repeatable, one-time setup scripts in Laravel projects.

The tool helps developers quickly configure common Laravel backend foundations such as Fortify backend auth, Sanctum SPA auth, API project structure, queue setup, UUID model defaults, and other recurring setup tasks.

Unlike generic scaffolding tools, Laraprep is designed around three principles:

1. **Backend-first setup**
2. **No frontend assumptions**
3. **Safe, inspectable code modifications**

The package provides CLI commands that can be run inside an existing Laravel project. Each command detects the current project state, previews planned changes, applies only necessary modifications, creates backups, and prints clear next steps.

The initial flagship command is:

`vendor/bin/laraprep fortify:backend`

This sets up Laravel Fortify as a backend-only authentication layer without generating views, Blade templates, frontend components, or starter-kit UI.

---

## 2. Problem Statement

Laravel’s documentation explains how to install and configure packages such as Fortify and Sanctum, but developers often need to manually stitch together many small setup steps.

For example, setting up Fortify backend-only authentication in a fresh Laravel project can involve:

* Installing the package
* Running the installer
* Publishing config and migrations
* Enabling the desired features
* Ensuring the provider is registered
* Updating the `User` model for email verification
* Checking routes
* Running migrations
* Understanding which endpoints the frontend should call

This is not difficult, but it is repetitive and easy to partially misconfigure.

Starter kits solve this by generating a full frontend and opinionated application structure. But many developers do not want that. They may be using:

* A separate frontend such as SvelteKit, Vue, React, or mobile clients
* Their own Blade or Livewire views
* An API-first Laravel backend
* Inertia with custom pages
* A package-based or domain-driven folder structure

The gap is a tool that can set up the backend pieces only, safely and transparently.

---

## 3. Target Users

### Primary user

A Laravel developer who wants to quickly configure backend infrastructure without adopting a starter kit.

Typical profile:

* Comfortable with PHP and Laravel
* Wants to avoid copying boilerplate repeatedly
* Wants generated setup to be understandable
* Does not want UI scaffolding forced into the project
* May be building APIs, SPAs, admin tools, or custom Laravel apps

### Secondary users

* Freelancers setting up many similar Laravel projects
* Package authors building opinionated Laravel foundations
* Teams wanting repeatable internal project setup standards
* Developers learning Laravel who want guided setup without hidden magic

---

## 4. Goals

### Product goals

* Provide quick, repeatable Laravel setup commands
* Keep generated changes backend-only unless explicitly requested
* Make every operation inspectable before it is applied
* Use AST-based code modification where possible
* Avoid brittle string replacement for PHP source files
* Make commands idempotent so they can be safely re-run
* Support both fresh Laravel apps and lightly customized existing apps
* Print clear summaries and next steps after each command

### Developer experience goals

* Install with Composer
* Run through a simple CLI binary
* Default to dry-run mode
* Require explicit `--apply` before modifying files
* Create backups before changing existing files
* Clearly show what was changed, skipped, or failed
* Avoid touching unrelated files
* Avoid creating frontend files unless the command explicitly says so

---

## 5. Non-Goals

The first version will not:

* Generate full frontend authentication pages
* Replace Laravel Breeze, Jetstream, or other starter kits
* Provide a visual UI builder
* Support every Laravel package
* Rewrite entire applications
* Enforce a specific architecture across the whole project
* Modify files without showing a dry-run preview first
* Support non-Laravel PHP projects
* Become a general-purpose refactoring framework

This is not a “magic app generator.” It is a safe setup assistant for common Laravel backend tasks.

---

## 6. Core Concept

Laraprep provides small setup commands.

Each command follows this lifecycle:

1. Detect project root
2. Detect Laravel version
3. Inspect current project state
4. Build a change plan
5. Show dry-run output
6. Apply changes only when `--apply` is passed
7. Back up modified files
8. Run required Composer or Artisan commands
9. Print result summary
10. Print next steps

Example usage:

`vendor/bin/laraprep fortify:backend`

Default behavior:

* Shows dry-run only
* Does not modify files

Apply behavior:

`vendor/bin/laraprep fortify:backend --apply`

This performs the changes.

---

## 7. Initial Commands

### 7.1 `fortify:backend`

Sets up Laravel Fortify for backend-only authentication.

This command must not generate frontend views.

#### Default features

By default, it enables:

* Registration
* Login
* Logout
* Password reset
* Email verification

It does not enable by default:

* Two-factor authentication
* Passkeys
* Profile management views
* Password confirmation views
* Blade auth pages

#### Example

`vendor/bin/laraprep fortify:backend --apply`

#### Options

`--registration`

Enable registration.

`--no-registration`

Disable registration.

`--reset-passwords`

Enable password reset support.

`--no-reset-passwords`

Disable password reset support.

`--verify-email`

Enable email verification.

`--no-verify-email`

Disable email verification.

`--two-factor`

Enable Fortify two-factor authentication backend.

`--passkeys`

Enable Fortify passkey support if available in the installed Fortify version.

`--no-migrate`

Skip running migrations.

`--no-backup`

Do not create backup files.

`--force`

Apply changes even if the project appears customized.

`--apply`

Actually modify the project.

#### Files affected

Potentially modified:

* `composer.json`
* `composer.lock`
* `config/fortify.php`
* `app/Models/User.php`
* `bootstrap/providers.php`
* Fortify published action classes
* Fortify published migrations

Potentially created by Fortify itself:

* `app/Actions/Fortify/CreateNewUser.php`
* `app/Actions/Fortify/UpdateUserPassword.php`
* `app/Actions/Fortify/PasswordValidationRules.php`
* `app/Providers/FortifyServiceProvider.php`
* `config/fortify.php`
* Required migrations

Laraprep itself should not create auth views.

#### Expected result

After successful setup, the Laravel project should have backend Fortify routes available for:

* `POST /register`
* `POST /login`
* `POST /logout`
* `POST /forgot-password`
* `POST /reset-password`
* `POST /email/verification-notification`
* Email verification signed URL handling

Actual available routes should be confirmed by `php artisan route:list`.

---

### 7.2 `sanctum:spa`

Sets up Laravel Sanctum for session-based SPA authentication.

This command is intended for frontends such as:

* SvelteKit
* Vue
* React
* Nuxt
* Next.js
* Mobile-like clients using browser sessions

#### Example

`vendor/bin/laraprep sanctum:spa --frontend=http://localhost:5173 --apply`

#### Responsibilities

* Install Sanctum if missing
* Publish Sanctum config if needed
* Configure stateful domains
* Help configure CORS credentials
* Print frontend request flow
* Explain CSRF cookie usage
* Ensure session middleware expectations are clear

#### Options

`--frontend=<url>`

Frontend URL to add to stateful domains and CORS config.

`--domain=<domain>`

Application domain.

`--apply`

Apply changes.

#### Non-goals

This command does not create frontend login forms or JavaScript fetch wrappers in v1.

---

### 7.3 `api:foundation`

Creates a minimal API-first Laravel backend structure.

#### Example

`vendor/bin/laraprep api:foundation --apply`

#### Possible generated structure

* `app/Actions`
* `app/Data`
* `app/Http/Resources`
* `app/Http/Controllers/Api`
* `app/Support`
* `routes/api.php` if missing or minimal

#### Responsibilities

* Ensure API routes are present
* Optionally add a health endpoint
* Optionally configure JSON exception responses
* Provide a clean place for actions, DTOs, and resources

#### Non-goals

This command does not impose full DDD structure in v1.

---

## 8. Technical Requirements

### 8.1 Composer package

The tool should be installable through Composer.

Package type:

`library`

Binary name:

`laraprep`

Example installation:

`composer require vendor/laraprep --dev`

Example usage:

`vendor/bin/laraprep fortify:backend`

### 8.2 Dependencies

Required:

* PHP 8.3 or later
* Symfony Console
* Symfony Process
* nikic/php-parser

Optional later:

* Laravel Prompts
* Rector
* Laravel Pint

### 8.3 Suggested `composer.json`

{
"name": "vendor/laraprep",
"description": "Safe backend setup scripts and codemods for Laravel projects.",
"type": "library",
"require": {
"php": "^8.3",
"symfony/console": "^7.0",
"symfony/process": "^7.0",
"nikic/php-parser": "^5.0"
},
"autoload": {
"psr-4": {
"Laraprep\": "src/"
}
},
"bin": [
"bin/laraprep"
]
}

---

## 9. Architecture

### 9.1 Folder structure

laraprep/
├── bin/
│   └── laraprep
├── src/
│   ├── Console/
│   │   └── Application.php
│   ├── Commands/
│   │   ├── FortifyBackendCommand.php
│   │   ├── SanctumSpaCommand.php
│   │   └── ApiFoundationCommand.php
│   ├── Project/
│   │   ├── LaravelProject.php
│   │   ├── ProjectDetector.php
│   │   └── LaravelVersion.php
│   ├── Planning/
│   │   ├── ChangePlan.php
│   │   ├── ChangeStep.php
│   │   └── ChangeReporter.php
│   ├── Files/
│   │   ├── FileEditor.php
│   │   ├── FileBackup.php
│   │   └── MarkerBlock.php
│   ├── Process/
│   │   ├── ComposerRunner.php
│   │   └── ArtisanRunner.php
│   ├── Ast/
│   │   ├── PhpFileEditor.php
│   │   ├── EnsureImport.php
│   │   ├── EnsureInterface.php
│   │   ├── EnsureProviderRegistration.php
│   │   └── EnsureConfigArrayValue.php
│   └── Support/
│       ├── Result.php
│       └── ConsoleOutput.php
├── tests/
├── composer.json
└── README.md

---

## 10. Change Planning System

Before applying changes, every command builds a `ChangePlan`.

A `ChangePlan` contains a list of steps.

Each step includes:

* Step name
* Step type
* Target file or command
* Current status
* Whether it will modify files
* Whether it is safe to re-run
* Whether it requires backup
* Dry-run description
* Apply callback

Example steps for `fortify:backend`:

1. Check Laravel project root
2. Check Composer availability
3. Install `laravel/fortify`
4. Run `php artisan fortify:install`
5. Ensure `FortifyServiceProvider` is registered
6. Configure Fortify features
7. Add `MustVerifyEmail` to `User` model
8. Run migrations
9. Clear caches
10. Print backend routes summary

---

## 11. Dry-Run Behavior

Dry-run is the default.

When a user runs:

`vendor/bin/laraprep fortify:backend`

The tool should print something like:

Planned changes:

* Would install `laravel/fortify`
* Would run `php artisan fortify:install`
* Would modify `config/fortify.php`
* Would modify `app/Models/User.php`
* Would verify `bootstrap/providers.php`
* Would run `php artisan migrate`
* Would run `php artisan optimize:clear`

No files were changed.

To apply these changes, run:

`vendor/bin/laraprep fortify:backend --apply`

---

## 12. Apply Behavior

When `--apply` is passed, the tool performs the planned changes.

Before modifying an existing file, it creates a backup unless `--no-backup` is passed.

Backup naming format:

`app/Models/User.php.laraprep.bak`

If a backup already exists, append timestamp:

`app/Models/User.php.laraprep.2026-06-28-1542.bak`

The tool should never silently overwrite backups.

---

## 13. AST Editing Requirements

PHP source files should be modified using AST where practical.

### 13.1 `User.php`

For email verification, the tool must:

* Find `App\Models\User`
* Add import for `Illuminate\Contracts\Auth\MustVerifyEmail` if missing
* Add `implements MustVerifyEmail` if missing
* Avoid duplicate imports
* Avoid duplicate interfaces
* Preserve existing traits, methods, and properties

Example target result:

`class User extends Authenticatable implements MustVerifyEmail`

If the class already implements other interfaces, preserve them and append `MustVerifyEmail`.

Example:

`class User extends Authenticatable implements Auditable, MustVerifyEmail`

### 13.2 `bootstrap/providers.php`

The tool must ensure that `App\Providers\FortifyServiceProvider::class` is registered if needed.

It should avoid duplicate provider entries.

### 13.3 `config/fortify.php`

In v1, this can be modified through careful structured text replacement.

In v2, this should move to AST or a safer PHP config array editor.

### 13.4 Route files

In v1, route additions should use marker blocks instead of AST.

Example:

// Laraprep: fortify backend test route
Route::get('/fortify-backend-test', function () {
return response()->json([
'authenticated' => auth()->check(),
'user' => auth()->user(),
]);
})->middleware('auth');
// End Laraprep: fortify backend test route

However, the default `fortify:backend` command should not add test routes unless the user passes:

`--test-route`

---

## 14. Idempotency Requirements

All commands must be safe to run more than once.

The tool must detect and skip already-completed work.

Examples:

* If `laravel/fortify` is already installed, skip install
* If `config/fortify.php` exists, do not blindly rerun install unless needed
* If `MustVerifyEmail` already exists on `User`, skip
* If provider is already registered, skip
* If route marker already exists, skip
* If migration files already exist, skip publishing them again unless forced

The summary should distinguish:

* Created
* Modified
* Skipped
* Failed
* Needs manual review

---

## 15. Safety Rules

The tool must not:

* Delete files
* Modify frontend files by default
* Modify `.env` without explicit permission
* Overwrite existing files without backup
* Apply changes in dry-run mode
* Run destructive Artisan commands
* Hide command failures
* Guess aggressively when project structure is unfamiliar

If the project looks too customized, the tool should stop and print manual instructions unless `--force` is passed.

Examples of “too customized”:

* Missing `app/Models/User.php`
* Multiple user models detected
* `bootstrap/providers.php` is not parseable
* Laravel version unsupported
* Fortify already installed but config is heavily modified
* Project is not detected as Laravel

---

## 16. Output Requirements

The CLI output should be clear and boring.

Example successful output:

Laraprep

Command: fortify:backend
Mode: apply

Completed:

✓ Installed laravel/fortify
✓ Published Fortify files
✓ Enabled registration
✓ Enabled password resets
✓ Enabled email verification
✓ Updated App\Models\User
✓ Registered FortifyServiceProvider
✓ Ran migrations
✓ Cleared caches

Skipped:

* Auth views were not generated
* Two-factor authentication was not enabled
* Passkeys were not enabled

Backend endpoints:

* POST /register
* POST /login
* POST /logout
* POST /forgot-password
* POST /reset-password
* POST /email/verification-notification

Next steps:

1. Run `php artisan route:list` to inspect registered routes.
2. Build your own frontend forms or SPA calls.
3. If using a separate SPA, run `vendor/bin/laraprep sanctum:spa`.

---

## 17. Frontend Policy

Laraprep is backend-first.

Commands must not generate frontend code unless explicitly named to do so.

The following files should not be created by `fortify:backend`:

* Blade auth views
* Livewire auth components
* Inertia pages
* Vue components
* React components
* Svelte components
* CSS files
* Layout files

If frontend scaffolding is added later, it must be a separate command, such as:

`auth:blade-views`

or:

`auth:sveltekit-client`

The backend command must stay clean.

---

## 18. Fortify Backend-Only Requirements

The `fortify:backend` command should configure Fortify without calling:

* `Fortify::loginView`
* `Fortify::registerView`
* `Fortify::requestPasswordResetLinkView`
* `Fortify::resetPasswordView`
* `Fortify::verifyEmailView`
* `Fortify::confirmPasswordView`

Those callbacks are only needed when Laravel serves the auth pages.

For backend-only usage, the frontend should call Fortify’s POST endpoints directly.

If Laravel tries to redirect to missing frontend routes, the user should be instructed to handle the flow in their frontend or explicitly add views themselves.

---

## 19. Error Handling

The tool should fail clearly.

Examples:

### Composer missing

Message:

Composer could not be found.
Install Composer or make sure it is available in your PATH.

### Not Laravel project

Message:

No Laravel project detected.
This command must be run from a directory containing an `artisan` file and `composer.json`.

### User model missing

Message:

Could not find `app/Models/User.php`.
Email verification could not be configured automatically.

Suggested manual change:

Add `Illuminate\Contracts\Auth\MustVerifyEmail` to your user model and implement the interface.

### PHP parse error

Message:

Could not parse `app/Models/User.php`.
No changes were applied to this file.
A backup was not needed because the file was not modified.

---

## 20. Testing Strategy

### Unit tests

Test individual services:

* Project detection
* Laravel version detection
* Change plan generation
* Backup naming
* Marker block insertion
* AST import insertion
* AST interface insertion
* Duplicate detection

### Fixture tests

Use fake Laravel project fixtures:

* Fresh Laravel 13 app
* Laravel app with Fortify already installed
* User model already implements `MustVerifyEmail`
* User model with existing interfaces
* Provider already registered
* Missing User model
* Customized provider file
* Broken PHP file

### Integration tests

Use temporary directories to:

* Create minimal Laravel-like fixture
* Run dry-run command
* Assert no files changed
* Run apply command
* Assert expected files changed
* Run apply command again
* Assert no duplicate changes

### Snapshot tests

Snapshot CLI output for dry-run and apply modes.

---

## 21. MVP Scope

The MVP should include only:

### Commands

* `fortify:backend`

### Core behavior

* Detect Laravel project
* Dry-run by default
* `--apply` mode
* Install Fortify
* Run Fortify installer
* Configure backend features
* Patch User model for email verification
* Ensure provider registration
* Run migrations unless disabled
* Clear caches
* Print endpoint summary
* No frontend files

### Safety

* Backups
* Idempotent re-runs
* Clear failure messages
* No destructive actions

### Technical

* Symfony Console
* Symfony Process
* nikic/php-parser
* PHPUnit or Pest tests

---

## 22. Post-MVP Scope

### Commands

* `sanctum:spa`
* `api:foundation`
* `queue:database`
* `queue:redis`
* `model:uuid-defaults`
* `pest:setup`
* `telescope:local`
* `horizon:redis`
* `docker:laravel-prod`
* `auth:blade-views`
* `auth:sveltekit-client`

### Features

* Interactive prompts
* JSON output mode
* Configurable project standards
* Internal company presets
* Rollback command
* Generated Markdown setup report
* Rector-based codemods
* Laravel Pint formatting after changes
* Version-specific recipes

---

## 23. Success Metrics

### MVP success

* A user can run one command in a fresh Laravel project and get backend Fortify configured
* No frontend files are generated
* Running the command twice does not duplicate changes
* The tool clearly reports what changed
* The tool safely handles a missing or already-modified User model
* The tool is useful without requiring users to understand all Fortify internals first

### Package success

* Developers use it repeatedly across new Laravel projects
* The dry-run output is trusted
* The AST modifications do not corrupt user files
* The command reduces setup time without hiding important details
* The package becomes a reliable personal or team bootstrap tool

---

## 24. Risks

### Risk: AST printer reformats PHP files

Mitigation:

* Keep AST changes small
* Run Laravel Pint after changes optionally
* Warn users that formatting may change
* Keep backups

### Risk: Laravel version differences

Mitigation:

* Detect Laravel version
* Maintain version-specific recipes
* Start with Laravel 13 support only
* Fail clearly on unsupported versions

### Risk: Fortify changes over time

Mitigation:

* Pin supported Fortify versions
* Add integration tests
* Keep command output based on actual route detection where possible

### Risk: Existing app customization

Mitigation:

* Detect unusual project states
* Prefer skipping and printing manual instructions over guessing
* Add `--force` for advanced users

### Risk: Too much scope

Mitigation:

* MVP only supports `fortify:backend`
* No frontend scaffolding in MVP
* Add commands one at a time

---

## 25. Open Questions

1. Should the package be Laravel 13 only at first, or support Laravel 12 and 13?
2. Should `fortify:backend` enable email verification by default?
3. Should migrations run automatically in `--apply`, or should the tool only print the command?
4. Should `.env` ever be modified automatically?
5. Should the tool support interactive prompts, or stay fully option-based?
6. Should the package use Rector internally later?
7. Should the command be available as an Artisan command too, or only as a Composer binary?
8. Should there be a rollback feature in v1?
9. Should generated backup files be gitignored automatically?
10. Should the package generate a setup report file, such as `laraprep-report.md`?

---

## 26. Recommended MVP Decision

The first version should be intentionally narrow:

Product name:

Laraprep

First command:

`fortify:backend`

Install style:

`composer require vendor/laraprep --dev`

Run style:

`vendor/bin/laraprep fortify:backend`

Apply style:

`vendor/bin/laraprep fortify:backend --apply`

Default behavior:

Dry-run only

Frontend behavior:

No frontend files, no auth views, no components

Code modification strategy:

AST for PHP source files
Structured file edits for config where acceptable
Backups before modification

Supported Laravel version:

Laravel 13 first

Core value:

Set up Laravel Fortify backend auth safely, quickly, and without dragging in a starter kit frontend.

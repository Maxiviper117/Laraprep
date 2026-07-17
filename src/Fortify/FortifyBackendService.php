<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Fortify;

use Maxiviper117\Laraprep\Ast\EditorResult;
use Maxiviper117\Laraprep\Ast\FortifyConfigEditor;
use Maxiviper117\Laraprep\Ast\ProvidersFileEditor;
use Maxiviper117\Laraprep\Ast\UserModelEditor;
use Maxiviper117\Laraprep\Files\FileBackup;
use Maxiviper117\Laraprep\Files\MarkerBlock;
use Maxiviper117\Laraprep\Planning\ChangePlan;
use Maxiviper117\Laraprep\Planning\ChangeResult;
use Maxiviper117\Laraprep\Planning\ChangeStatus;
use Maxiviper117\Laraprep\Process\ArtisanRunner;
use Maxiviper117\Laraprep\Process\CommandResult;
use Maxiviper117\Laraprep\Process\ComposerRunner;
use Maxiviper117\Laraprep\Project\LaravelProject;
use PhpParser\Error;
use RuntimeException;

final readonly class FortifyBackendService
{
    public function __construct(
        private ComposerRunner $composerRunner,
        private ArtisanRunner $artisanRunner,
        private FileBackup $fileBackup,
        private MarkerBlock $markerBlock,
        private UserModelEditor $userModelEditor,
        private ProvidersFileEditor $providersFileEditor,
        private FortifyConfigEditor $fortifyConfigEditor
    ) {}

    public function plan(LaravelProject $project, FortifyBackendOptions $options): ChangePlan
    {
        $results = [];

        $fortifyInstalled = $this->fortifyInstalled($project);
        $installerRequired = ! is_file($project->fortifyConfigPath) || ! is_file($project->fortifyServiceProviderPath);
        $userModelPath = $project->path('app/Models/User.php');

        $results[] = $fortifyInstalled
            ? new ChangeResult('Install laravel/fortify', ChangeStatus::Skipped, 'laravel/fortify is already installed.', $project->composerJsonPath)
            : new ChangeResult('Install laravel/fortify', ChangeStatus::Planned, 'Would install laravel/fortify with Composer.', $project->composerJsonPath);

        $results[] = $installerRequired
            ? new ChangeResult('Run Fortify installer', ChangeStatus::Planned, 'Would run php artisan fortify:install.', $project->artisanPath)
            : new ChangeResult('Run Fortify installer', ChangeStatus::Skipped, 'Fortify published files already exist.', $project->artisanPath);

        if ($options->passkeys && $fortifyInstalled && ! $this->passkeysSupported($project)) {
            $results[] = new ChangeResult(
                'Enable passkeys',
                ChangeStatus::ManualReview,
                'Installed Fortify version does not appear to support passkeys.',
                $project->fortifyConfigPath
            );
        } else {
            $results[] = new ChangeResult(
                'Configure Fortify features',
                ChangeStatus::Planned,
                'Would configure backend-only Fortify features in config/fortify.php.',
                $project->fortifyConfigPath
            );
        }

        if ($options->verifyEmail) {
            if (! is_file($userModelPath)) {
                $results[] = new ChangeResult(
                    'Update App\Models\User',
                    ChangeStatus::ManualReview,
                    'Could not find app/Models/User.php. Email verification must be configured manually.',
                    $userModelPath
                );
            } else {
                $results[] = new ChangeResult(
                    'Update App\Models\User',
                    ChangeStatus::Planned,
                    'Would add MustVerifyEmail to App\Models\User if missing.',
                    $userModelPath
                );
            }
        } else {
            $results[] = new ChangeResult(
                'Update App\Models\User',
                ChangeStatus::Skipped,
                'Email verification is disabled.',
                $userModelPath
            );
        }

        $results[] = new ChangeResult(
            'Register FortifyServiceProvider',
            ChangeStatus::Planned,
            'Would ensure App\Providers\FortifyServiceProvider::class is registered in bootstrap/providers.php.',
            $project->providersPath
        );

        $results[] = $options->testRoute
            ? new ChangeResult('Add Fortify backend test route', ChangeStatus::Planned, 'Would append an optional auth test route marker block.', $project->routesWebPath)
            : new ChangeResult('Add Fortify backend test route', ChangeStatus::Skipped, 'No test route requested.', $project->routesWebPath);

        $results[] = $options->migrate
            ? new ChangeResult('Run migrations', ChangeStatus::Planned, 'Would run php artisan migrate.', $project->artisanPath)
            : new ChangeResult('Run migrations', ChangeStatus::Skipped, 'Migration execution disabled with --no-migrate.', $project->artisanPath);

        $results[] = new ChangeResult('Clear caches', ChangeStatus::Planned, 'Would run php artisan optimize:clear.', $project->artisanPath);

        return new ChangePlan(
            results: $results,
            endpoints: $this->expectedEndpoints($options),
            nextSteps: $this->nextSteps($options),
            applyMode: false
        );
    }

    public function apply(LaravelProject $project, FortifyBackendOptions $options): ChangePlan
    {
        $results = [];
        $partialFailure = false;
        $fortifyInstalled = $this->fortifyInstalled($project);

        if (! $fortifyInstalled) {
            $this->backupIfNeeded($project->composerJsonPath, $options);

            if (is_file($project->composerLockPath)) {
                $this->backupIfNeeded($project->composerLockPath, $options);
            }

            $result = $this->composerRunner->require($project->root, 'laravel/fortify');
            $results[] = $this->fromCommandResult($result, 'Install laravel/fortify', 'Installed laravel/fortify.', $project->composerJsonPath);

            if (! $result->succeeded()) {
                return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
            }
        } else {
            $results[] = new ChangeResult('Install laravel/fortify', ChangeStatus::Skipped, 'laravel/fortify is already installed.', $project->composerJsonPath);
        }

        if ($options->passkeys && ! $this->passkeysSupported($project)) {
            $results[] = new ChangeResult(
                'Enable passkeys',
                ChangeStatus::ManualReview,
                'Installed Fortify version does not appear to support passkeys.',
                $project->fortifyConfigPath
            );

            return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
        }

        if (! is_file($project->fortifyConfigPath) || ! is_file($project->fortifyServiceProviderPath)) {
            $result = $this->artisanRunner->run($project->root, ['fortify:install']);
            $results[] = $this->fromCommandResult($result, 'Run Fortify installer', 'Published Fortify files.', $project->artisanPath);

            if (! $result->succeeded()) {
                return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
            }
        } else {
            $results[] = new ChangeResult('Run Fortify installer', ChangeStatus::Skipped, 'Fortify published files already exist.', $project->artisanPath);
        }

        $configResult = $this->editPhpFile(
            $project->fortifyConfigPath,
            $options,
            fn (string $contents): EditorResult => $this->fortifyConfigEditor->sync($contents, $options),
            'Configure Fortify features'
        );
        $results[] = $configResult;

        if ($configResult->isProblem()) {
            return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
        }

        if ($options->verifyEmail) {
            $userModelPath = $project->path('app/Models/User.php');

            if (! is_file($userModelPath)) {
                $results[] = new ChangeResult(
                    'Update App\Models\User',
                    ChangeStatus::ManualReview,
                    'Could not find app/Models/User.php. Email verification must be configured manually.',
                    $userModelPath
                );

                return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
            }

            $userResult = $this->editPhpFile(
                $userModelPath,
                $options,
                fn (string $contents): EditorResult => $this->userModelEditor->ensureMustVerifyEmail($contents),
                'Update App\Models\User'
            );
            $results[] = $userResult;

            if ($userResult->isProblem()) {
                return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
            }
        } else {
            $results[] = new ChangeResult('Update App\Models\User', ChangeStatus::Skipped, 'Email verification is disabled.', $project->path('app/Models/User.php'));
        }

        $providerResult = $this->editPhpFile(
            $project->providersPath,
            $options,
            fn (string $contents): EditorResult => $this->providersFileEditor->ensureFortifyProvider($contents),
            'Register FortifyServiceProvider'
        );
        $results[] = $providerResult;

        if ($providerResult->isProblem()) {
            return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
        }

        if ($options->testRoute) {
            $routeResult = $this->appendTestRoute($project, $options);
            $results[] = $routeResult;

            if ($routeResult->isProblem()) {
                return new ChangePlan($results, $this->expectedEndpoints($options), $this->nextSteps($options), true, true);
            }
        } else {
            $results[] = new ChangeResult('Add Fortify backend test route', ChangeStatus::Skipped, 'No test route requested.', $project->routesWebPath);
        }

        if ($options->migrate) {
            $result = $this->artisanRunner->run($project->root, ['migrate', '--force']);
            $results[] = $this->fromCommandResult($result, 'Run migrations', 'Ran php artisan migrate.', $project->artisanPath);

            if (! $result->succeeded()) {
                $partialFailure = true;
            }
        } else {
            $results[] = new ChangeResult('Run migrations', ChangeStatus::Skipped, 'Migration execution disabled with --no-migrate.', $project->artisanPath);
        }

        if (! $partialFailure) {
            $result = $this->artisanRunner->run($project->root, ['optimize:clear']);
            $results[] = $this->fromCommandResult($result, 'Clear caches', 'Ran php artisan optimize:clear.', $project->artisanPath);

            if (! $result->succeeded()) {
                $partialFailure = true;
            }
        } else {
            $results[] = new ChangeResult('Clear caches', ChangeStatus::Skipped, 'Skipped because a previous apply step failed.', $project->artisanPath);
        }

        return new ChangePlan(
            results: $results,
            endpoints: $this->expectedEndpoints($options),
            nextSteps: $this->nextSteps($options),
            applyMode: true,
            partialFailure: $partialFailure
        );
    }

    private function fortifyInstalled(LaravelProject $project): bool
    {
        if (! is_file($project->composerJsonPath)) {
            return false;
        }

        $composerJson = json_decode((string) file_get_contents($project->composerJsonPath), true);

        if (! is_array($composerJson)) {
            return false;
        }

        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerJson[$section]) && is_array($composerJson[$section]) && isset($composerJson[$section]['laravel/fortify'])) {
                return true;
            }
        }

        return false;
    }

    private function passkeysSupported(LaravelProject $project): bool
    {
        $featuresPath = $project->path('vendor/laravel/fortify/src/Features.php');

        if (! is_file($featuresPath)) {
            return false;
        }

        return str_contains((string) file_get_contents($featuresPath), 'passkeys');
    }

    private function backupIfNeeded(string $path, FortifyBackendOptions $options): void
    {
        if ($options->backup && is_file($path)) {
            $this->fileBackup->backup($path);
        }
    }

    /**
     * @param  callable(string): EditorResult  $editor
     */
    private function editPhpFile(string $path, FortifyBackendOptions $options, callable $editor, string $name): ChangeResult
    {
        if (! is_file($path)) {
            return new ChangeResult($name, ChangeStatus::ManualReview, sprintf('Could not find %s.', $path), $path);
        }

        try {
            $edited = $editor((string) file_get_contents($path));
        } catch (Error|RuntimeException $exception) {
            return new ChangeResult($name, ChangeStatus::Failed, $exception->getMessage(), $path);
        }

        if (! $edited->changed) {
            return new ChangeResult($name, ChangeStatus::Skipped, $edited->message ?? 'No changes were required.', $path);
        }

        $this->backupIfNeeded($path, $options);
        file_put_contents($path, $edited->contents);

        return new ChangeResult($name, ChangeStatus::Applied, $edited->message ?? 'Updated file.', $path);
    }

    private function appendTestRoute(LaravelProject $project, FortifyBackendOptions $options): ChangeResult
    {
        if (! is_file($project->routesWebPath)) {
            return new ChangeResult(
                'Add Fortify backend test route',
                ChangeStatus::ManualReview,
                'Could not find routes/web.php.',
                $project->routesWebPath
            );
        }

        $contents = (string) file_get_contents($project->routesWebPath);
        $updated = $this->markerBlock->appendIfMissing($contents, 'fortify backend test route', <<<'PHP'
Route::get('/fortify-backend-test', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->user(),
    ]);
})->middleware('auth');
PHP);

        if ($updated === $contents) {
            return new ChangeResult('Add Fortify backend test route', ChangeStatus::Skipped, 'Fortify backend test route already exists.', $project->routesWebPath);
        }

        $this->backupIfNeeded($project->routesWebPath, $options);
        file_put_contents($project->routesWebPath, $updated);

        return new ChangeResult('Add Fortify backend test route', ChangeStatus::Applied, 'Added the optional backend auth test route.', $project->routesWebPath);
    }

    private function fromCommandResult(CommandResult $result, string $name, string $successMessage, string $target): ChangeResult
    {
        if ($result->succeeded()) {
            return new ChangeResult($name, ChangeStatus::Applied, $successMessage, $target);
        }

        return new ChangeResult(
            $name,
            ChangeStatus::Failed,
            trim($result->output) === '' ? 'The command failed without output.' : $result->output,
            $target
        );
    }

    /**
     * @return list<string>
     */
    private function expectedEndpoints(FortifyBackendOptions $options): array
    {
        $endpoints = [
            'POST /login',
            'POST /logout',
        ];

        if ($options->registration) {
            $endpoints[] = 'POST /register';
        }

        if ($options->resetPasswords) {
            $endpoints[] = 'POST /forgot-password';
            $endpoints[] = 'POST /reset-password';
        }

        if ($options->verifyEmail) {
            $endpoints[] = 'POST /email/verification-notification';
            $endpoints[] = 'GET /email/verify/{id}/{hash}';
        }

        return $endpoints;
    }

    /**
     * @return list<string>
     */
    private function nextSteps(FortifyBackendOptions $options): array
    {
        $steps = [
            'Run php artisan route:list to confirm the registered backend routes.',
            'Build your own frontend forms or SPA calls against the Fortify endpoints.',
        ];

        if (! $options->twoFactor) {
            $steps[] = 'Re-run with --two-factor if you want Fortify two-factor authentication enabled.';
        }

        return $steps;
    }
}

<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Project;

use Maxiviper117\Laraprep\Support\CommandFailure;
use Maxiviper117\Laraprep\Support\ExitCode;

final class ProjectDetector
{
    public function detect(string $root): LaravelProject
    {
        $composerJsonPath = $root.DIRECTORY_SEPARATOR.'composer.json';
        $artisanPath = $root.DIRECTORY_SEPARATOR.'artisan';

        if (! is_file($composerJsonPath) || ! is_file($artisanPath)) {
            throw new CommandFailure(
                'No Laravel project detected. This command must be run from a directory containing an artisan file and composer.json.',
                ExitCode::UNSUPPORTED_PROJECT
            );
        }

        $majorVersion = $this->detectLaravelMajorVersion($composerJsonPath, $root.DIRECTORY_SEPARATOR.'composer.lock', $root);

        if ($majorVersion !== 13) {
            throw new CommandFailure(
                sprintf('Unsupported Laravel version detected: %d. Laraprep currently supports Laravel 13 only.', $majorVersion),
                ExitCode::UNSUPPORTED_PROJECT
            );
        }

        return new LaravelProject(
            root: $root,
            laravelMajorVersion: $majorVersion,
            artisanPath: $artisanPath,
            composerJsonPath: $composerJsonPath,
            composerLockPath: $root.DIRECTORY_SEPARATOR.'composer.lock',
            providersPath: $root.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'providers.php',
            fortifyConfigPath: $root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'fortify.php',
            fortifyServiceProviderPath: $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'FortifyServiceProvider.php',
            routesWebPath: $root.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php',
        );
    }

    private function detectLaravelMajorVersion(string $composerJsonPath, string $composerLockPath, string $root): int
    {
        $lockVersion = $this->detectVersionFromComposerLock($composerLockPath);

        if ($lockVersion !== null) {
            return $lockVersion;
        }

        $installedVersion = $this->detectVersionFromInstalledPackages($root);

        if ($installedVersion !== null) {
            return $installedVersion;
        }

        $composerJson = json_decode((string) file_get_contents($composerJsonPath), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($composerJson)) {
            throw new CommandFailure(
                'Could not parse composer.json while detecting the Laravel version.',
                ExitCode::UNSUPPORTED_PROJECT
            );
        }

        foreach (['require', 'require-dev'] as $section) {
            if (! isset($composerJson[$section]) || ! is_array($composerJson[$section]) || ! isset($composerJson[$section]['laravel/framework'])) {
                continue;
            }

            $version = $composerJson[$section]['laravel/framework'];

            if (! is_string($version)) {
                throw new CommandFailure(
                    'Could not parse the Laravel version requirement from composer.json.',
                    ExitCode::UNSUPPORTED_PROJECT
                );
            }

            return $this->extractMajorVersion($version);
        }

        throw new CommandFailure(
            'Could not detect the Laravel version from composer metadata.',
            ExitCode::UNSUPPORTED_PROJECT
        );
    }

    private function detectVersionFromComposerLock(string $composerLockPath): ?int
    {
        if (! is_file($composerLockPath)) {
            return null;
        }

        $lock = json_decode((string) file_get_contents($composerLockPath), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($lock)) {
            return null;
        }

        foreach (['packages', 'packages-dev'] as $section) {
            if (! isset($lock[$section]) || ! is_array($lock[$section])) {
                continue;
            }

            foreach ($lock[$section] as $package) {
                if (! is_array($package) || ($package['name'] ?? null) !== 'laravel/framework') {
                    continue;
                }

                $version = $package['version'] ?? null;

                if (! is_string($version)) {
                    throw new CommandFailure(
                        'Could not parse the Laravel version from composer.lock.',
                        ExitCode::UNSUPPORTED_PROJECT
                    );
                }

                return $this->extractMajorVersion($version);
            }
        }

        return null;
    }

    private function detectVersionFromInstalledPackages(string $root): ?int
    {
        $paths = [
            $root.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'installed.json',
            $root.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'installed.php',
        ];

        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }

            if (str_ends_with($path, '.php')) {
                $installed = require $path;
            } else {
                $installed = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            }

            if (! is_array($installed)) {
                continue;
            }

            $packages = isset($installed['packages']) && is_array($installed['packages'])
                ? $installed['packages']
                : $installed;

            foreach ($packages as $package) {
                if (! is_array($package) || ($package['name'] ?? null) !== 'laravel/framework') {
                    continue;
                }

                $version = $package['version'] ?? null;

                if (! is_string($version)) {
                    throw new CommandFailure(
                        'Could not parse the Laravel version from installed Composer metadata.',
                        ExitCode::UNSUPPORTED_PROJECT
                    );
                }

                return $this->extractMajorVersion($version);
            }
        }

        return null;
    }

    private function extractMajorVersion(string $version): int
    {
        if (! preg_match('/(\d+)/', $version, $matches)) {
            throw new CommandFailure(
                sprintf('Could not parse the Laravel version from "%s".', $version),
                ExitCode::UNSUPPORTED_PROJECT
            );
        }

        return (int) $matches[1];
    }
}

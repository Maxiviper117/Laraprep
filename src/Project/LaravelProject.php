<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Project;

final readonly class LaravelProject
{
    public function __construct(
        public string $root,
        public int $laravelMajorVersion,
        public string $artisanPath,
        public string $composerJsonPath,
        public string $composerLockPath,
        public string $providersPath,
        public string $fortifyConfigPath,
        public string $fortifyServiceProviderPath,
        public string $routesWebPath
    ) {}

    public function path(string $relativePath): string
    {
        return $this->root.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    }
}

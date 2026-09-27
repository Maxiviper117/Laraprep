<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Process;

final readonly class ArtisanRunner
{
    public function __construct(
        private ProcessRunner $processRunner
    ) {}

    /**
     * @param  list<string>  $arguments
     */
    public function run(string $workingDirectory, array $arguments): CommandResult
    {
        return $this->processRunner->run(['php', 'artisan', ...$arguments], $workingDirectory);
    }
}

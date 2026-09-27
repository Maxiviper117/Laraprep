<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Process;

final readonly class ComposerRunner
{
    public function __construct(
        private ProcessRunner $processRunner
    ) {}

    public function require(string $workingDirectory, string $package): CommandResult
    {
        return $this->processRunner->run(['composer', 'require', $package], $workingDirectory);
    }
}

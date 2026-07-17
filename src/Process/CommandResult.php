<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Process;

final readonly class CommandResult
{
    /**
     * @param  list<string>  $command
     */
    public function __construct(
        public array $command,
        public int $exitCode,
        public string $output
    ) {}

    public function succeeded(): bool
    {
        return $this->exitCode === 0;
    }
}

<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Process;

use Symfony\Component\Process\Process;

class ProcessRunner
{
    /**
     * @param  list<string>  $command
     */
    public function run(array $command, string $workingDirectory): CommandResult
    {
        $process = new Process($command, $workingDirectory);
        $process->run();

        return new CommandResult(
            command: $command,
            exitCode: $process->getExitCode() ?? 1,
            output: trim($process->getOutput().$process->getErrorOutput()),
        );
    }
}

<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Support;

use RuntimeException;

final class CommandFailure extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $exitCode
    ) {
        parent::__construct($message);
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }
}

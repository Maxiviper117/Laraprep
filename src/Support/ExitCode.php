<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Support;

final class ExitCode
{
    public const int SUCCESS = 0;

    public const int INVALID_USAGE = 2;

    public const int UNSUPPORTED_PROJECT = 3;

    public const int PLANNING_FAILURE = 4;

    public const int PARTIAL_APPLY_FAILURE = 5;
}

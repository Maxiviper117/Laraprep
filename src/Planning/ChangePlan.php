<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Planning;

final readonly class ChangePlan
{
    /**
     * @param  list<ChangeResult>  $results
     * @param  list<string>  $endpoints
     * @param  list<string>  $nextSteps
     */
    public function __construct(
        public array $results,
        public array $endpoints,
        public array $nextSteps,
        public bool $applyMode,
        public bool $partialFailure = false
    ) {}
}

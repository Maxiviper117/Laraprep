<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Planning;

final readonly class ChangeResult
{
    public function __construct(
        public string $name,
        public ChangeStatus $status,
        public string $message,
        public ?string $target = null
    ) {}

    public function isProblem(): bool
    {
        return $this->status === ChangeStatus::Failed || $this->status === ChangeStatus::ManualReview;
    }
}

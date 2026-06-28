<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Ast;

final readonly class EditorResult
{
    public function __construct(
        public bool $changed,
        public ?string $contents = null,
        public ?string $message = null
    ) {}

    public static function skipped(string $message): self
    {
        return new self(false, null, $message);
    }

    public static function changed(string $contents, string $message): self
    {
        return new self(true, $contents, $message);
    }
}

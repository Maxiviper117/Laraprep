<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Files;

final class MarkerBlock
{
    public function hasBlock(string $contents, string $name): bool
    {
        return str_contains($contents, $this->startMarker($name));
    }

    public function wrap(string $name, string $body): string
    {
        return $this->startMarker($name).PHP_EOL.$body.PHP_EOL.$this->endMarker($name);
    }

    public function appendIfMissing(string $contents, string $name, string $body): string
    {
        if ($this->hasBlock($contents, $name)) {
            return $contents;
        }

        $suffix = str_ends_with($contents, PHP_EOL) ? '' : PHP_EOL;

        return $contents.$suffix.PHP_EOL.$this->wrap($name, $body).PHP_EOL;
    }

    private function startMarker(string $name): string
    {
        return sprintf('// Laraprep: %s', $name);
    }

    private function endMarker(string $name): string
    {
        return sprintf('// End Laraprep: %s', $name);
    }
}

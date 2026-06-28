<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Files;

use RuntimeException;

final class FileBackup
{
    public function backup(string $path): string
    {
        if (! is_file($path)) {
            throw new RuntimeException(sprintf('Cannot back up missing file: %s', $path));
        }

        $backupPath = $this->backupPathFor($path);

        if (! copy($path, $backupPath)) {
            throw new RuntimeException(sprintf('Failed to create backup for %s', $path));
        }

        return $backupPath;
    }

    public function backupPathFor(string $path): string
    {
        $default = $path.'.laraprep.bak';

        if (! file_exists($default)) {
            return $default;
        }

        $timestamp = date('Y-m-d-Hi');
        $candidate = $path.'.laraprep.'.$timestamp.'.bak';
        $suffix = 1;

        while (file_exists($candidate)) {
            $candidate = $path.'.laraprep.'.$timestamp.'-'.$suffix.'.bak';
            $suffix++;
        }

        return $candidate;
    }
}

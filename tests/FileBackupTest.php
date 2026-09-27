<?php

declare(strict_types=1);

use Maxiviper117\Laraprep\Files\FileBackup;

it('creates a default backup path when no backup exists', function (): void {
    $path = fakeFile('app/Models/User.php', '<?php');

    expect((new FileBackup)->backupPathFor($path))
        ->toBe($path.'.laraprep.bak');
});

it('creates timestamped backup paths when the default name already exists', function (): void {
    $path = fakeFile('app/Models/User.php', '<?php');
    file_put_contents($path.'.laraprep.bak', 'existing');

    expect((new FileBackup)->backupPathFor($path))
        ->toMatch('/\.laraprep\.\d{4}-\d{2}-\d{2}-\d{4}\.bak$/');
});

<?php

declare(strict_types=1);

/**
 * @param  array<string, string>  $files
 */
function fakeLaravelProject(array $files): string
{
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laraprep-tests-'.bin2hex(random_bytes(8));
    mkdir($root, recursive: true);

    foreach ($files as $relativePath => $contents) {
        $path = $root.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        file_put_contents($path, $contents);
    }

    foreach (['bootstrap/providers.php', 'routes/web.php'] as $defaultFile) {
        $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $defaultFile);

        if (! is_file($path)) {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), recursive: true);
            }

            file_put_contents($path, "<?php\n\nreturn [];\n");
        }
    }

    return $root;
}

function fakeFile(string $relativePath, string $contents): string
{
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laraprep-tests-'.bin2hex(random_bytes(8));
    $path = $root.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);

    mkdir(dirname($path), recursive: true);
    file_put_contents($path, $contents);

    return $path;
}

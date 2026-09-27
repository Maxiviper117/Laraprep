<?php

declare(strict_types=1);

use Maxiviper117\Laraprep\Project\ProjectDetector;
use Maxiviper117\Laraprep\Support\CommandFailure;

it('detects a laravel 13 project from composer json metadata', function (): void {
    $root = fakeLaravelProject([
        'composer.json' => json_encode([
            'require' => [
                'php' => '^8.3',
                'laravel/framework' => '^13.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        'artisan' => '<?php',
    ]);

    $project = (new ProjectDetector)->detect($root);

    expect($project->laravelMajorVersion)->toBe(13)
        ->and($project->fortifyConfigPath)->toEndWith('config'.DIRECTORY_SEPARATOR.'fortify.php');
});

it('rejects unsupported laravel versions', function (): void {
    $root = fakeLaravelProject([
        'composer.json' => json_encode([
            'require' => [
                'php' => '^8.3',
                'laravel/framework' => '^12.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        'artisan' => '<?php',
    ]);

    (new ProjectDetector)->detect($root);
})->throws(CommandFailure::class);

<?php

declare(strict_types=1);

use Maxiviper117\Laraprep\Files\MarkerBlock;

it('appends a marker block only once', function (): void {
    $markerBlock = new MarkerBlock;
    $contents = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n";

    $first = $markerBlock->appendIfMissing($contents, 'fortify backend test route', "Route::get('/fortify-backend-test', fn () => null);");
    $second = $markerBlock->appendIfMissing($first, 'fortify backend test route', "Route::get('/fortify-backend-test', fn () => null);");

    expect(substr_count($second, 'fortify backend test route'))->toBe(2);
});

<?php

declare(strict_types=1);

use Maxiviper117\Laraprep\Ast\PhpFileEditor;
use Maxiviper117\Laraprep\Ast\ProvidersFileEditor;

it('registers the fortify service provider when missing', function (): void {
    $editor = new ProvidersFileEditor(new PhpFileEditor);

    $result = $editor->ensureFortifyProvider(<<<'PHP'
<?php

return [
    App\Providers\AppServiceProvider::class,
];
PHP);

    expect($result->changed)->toBeTrue()
        ->and($result->contents)->toContain('App\Providers\FortifyServiceProvider::class');
});

<?php

declare(strict_types=1);

use Maxiviper117\Laraprep\Ast\FortifyConfigEditor;
use Maxiviper117\Laraprep\Ast\PhpFileEditor;
use Maxiviper117\Laraprep\Fortify\FortifyBackendOptions;

it('configures fortify features for backend-only usage', function (): void {
    $editor = new FortifyConfigEditor(new PhpFileEditor);
    $options = new FortifyBackendOptions(
        apply: false,
        registration: true,
        resetPasswords: true,
        verifyEmail: true,
        twoFactor: true,
        passkeys: false,
        migrate: true,
        backup: true,
        force: false,
        testRoute: false,
    );

    $result = $editor->sync(<<<'PHP'
<?php

return [
    'views' => true,
    'features' => [],
];
PHP, $options);

    expect($result->changed)->toBeTrue()
        ->and($result->contents)->toContain("'views' => false")
        ->and($result->contents)->toContain('Features::registration()')
        ->and($result->contents)->toContain('Features::twoFactorAuthentication');
});

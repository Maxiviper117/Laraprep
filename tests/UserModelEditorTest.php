<?php

declare(strict_types=1);

use Maxiviper117\Laraprep\Ast\PhpFileEditor;
use Maxiviper117\Laraprep\Ast\UserModelEditor;

it('adds the must verify email import and interface to the user model', function (): void {
    $editor = new UserModelEditor(new PhpFileEditor);

    $result = $editor->ensureMustVerifyEmail(<<<'PHP'
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
}
PHP);

    expect($result->changed)->toBeTrue()
        ->and($result->contents)->toContain('use Illuminate\Contracts\Auth\MustVerifyEmail;')
        ->and($result->contents)->toContain('class User extends Authenticatable implements MustVerifyEmail');
});

it('skips user models that already implement must verify email', function (): void {
    $editor = new UserModelEditor(new PhpFileEditor);

    $result = $editor->ensureMustVerifyEmail(<<<'PHP'
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
}
PHP);

    expect($result->changed)->toBeFalse();
});

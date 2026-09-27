<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Ast;

use Maxiviper117\Laraprep\Fortify\FortifyBackendOptions;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use RuntimeException;

final readonly class FortifyConfigEditor
{
    public function __construct(
        private PhpFileEditor $phpFileEditor
    ) {}

    public function sync(string $contents, FortifyBackendOptions $options): EditorResult
    {
        $statements = $this->phpFileEditor->parse($contents);
        $config = $this->findConfigArray($statements);
        $changed = false;

        if ($this->ensureFeaturesImport($statements)) {
            $changed = true;
        }

        if ($this->setArrayEntry($config, 'views', new ConstFetch(new Name('false')))) {
            $changed = true;
        }

        if ($this->setArrayEntry($config, 'features', $this->featuresArray($options))) {
            $changed = true;
        }

        if (! $changed) {
            return EditorResult::skipped('config/fortify.php is already configured for the selected features.');
        }

        $printed = $this->phpFileEditor->print($statements);

        if ($printed === $contents) {
            return EditorResult::skipped('config/fortify.php is already configured for the selected features.');
        }

        return EditorResult::changed(
            $printed,
            'Updated config/fortify.php for backend-only Fortify features.'
        );
    }

    /**
     * @param  list<Node>  $statements
     */
    private function findConfigArray(array $statements): Array_
    {
        foreach ($statements as $statement) {
            if ($statement instanceof Return_ && $statement->expr instanceof Array_) {
                return $statement->expr;
            }
        }

        throw new RuntimeException('Could not parse config/fortify.php.');
    }

    /**
     * @param  list<Node>  $statements
     */
    private function ensureFeaturesImport(array &$statements): bool
    {
        foreach ($statements as $statement) {
            if (! $statement instanceof Use_) {
                continue;
            }

            foreach ($statement->uses as $use) {
                if ($use->name->toString() === 'Laravel\Fortify\Features') {
                    return false;
                }
            }
        }

        $offset = 0;

        foreach ($statements as $index => $statement) {
            if ($statement instanceof Return_) {
                $offset = $index;
                break;
            }
        }

        array_splice($statements, $offset, 0, [new Use_([new UseUse(new Name('Laravel\Fortify\Features'))])]);

        return true;
    }

    private function setArrayEntry(Array_ $array, string $key, Node\Expr $value): bool
    {
        foreach ($array->items as $item) {
            if (! $item->key instanceof String_) {
                continue;
            }

            if ($item->key->value !== $key) {
                continue;
            }

            if (serialize($item->value) === serialize($value)) {
                return false;
            }

            $item->value = $value;

            return true;
        }

        $array->items[] = new ArrayItem($value, new String_($key));

        return true;
    }

    private function featuresArray(FortifyBackendOptions $options): Array_
    {
        $items = [];

        if ($options->registration) {
            $items[] = new ArrayItem(new StaticCall(new Name('Features'), 'registration'));
        }

        if ($options->resetPasswords) {
            $items[] = new ArrayItem(new StaticCall(new Name('Features'), 'resetPasswords'));
        }

        if ($options->verifyEmail) {
            $items[] = new ArrayItem(new StaticCall(new Name('Features'), 'emailVerification'));
        }

        if ($options->twoFactor) {
            $items[] = new ArrayItem(
                new StaticCall(
                    new Name('Features'),
                    'twoFactorAuthentication',
                    [new Arg(new Array_([
                        new ArrayItem(new ConstFetch(new Name('true')), new String_('confirm')),
                        new ArrayItem(new ConstFetch(new Name('true')), new String_('confirmPassword')),
                    ]))]
                )
            );
        }

        if ($options->passkeys) {
            $items[] = new ArrayItem(new StaticCall(new Name('Features'), 'passkeys'));
        }

        return new Array_($items, ['kind' => Array_::KIND_SHORT]);
    }
}

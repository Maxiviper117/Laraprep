<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Ast;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Return_;
use RuntimeException;

final readonly class ProvidersFileEditor
{
    public function __construct(
        private PhpFileEditor $phpFileEditor
    ) {}

    public function ensureFortifyProvider(string $contents): EditorResult
    {
        $statements = $this->phpFileEditor->parse($contents);
        $providers = $this->findProvidersArray($statements);

        foreach ($providers->items as $item) {
            if (! $item->value instanceof ClassConstFetch) {
                continue;
            }

            if ($item->value->class instanceof Name && $item->value->class->toString() === 'App\Providers\FortifyServiceProvider') {
                return EditorResult::skipped('FortifyServiceProvider is already registered.');
            }
        }

        $providers->items[] = new ArrayItem(
            new ClassConstFetch(new Name('App\Providers\FortifyServiceProvider'), 'class')
        );

        return EditorResult::changed(
            $this->phpFileEditor->print($statements),
            'Registered App\Providers\FortifyServiceProvider in bootstrap/providers.php.'
        );
    }

    /**
     * @param  list<Node>  $statements
     */
    private function findProvidersArray(array $statements): Array_
    {
        foreach ($statements as $statement) {
            if ($statement instanceof Return_ && $statement->expr instanceof Array_) {
                return $statement->expr;
            }
        }

        throw new RuntimeException('Could not parse bootstrap/providers.php.');
    }
}

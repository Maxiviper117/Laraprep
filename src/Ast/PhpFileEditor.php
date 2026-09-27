<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Ast;

use PhpParser\Node;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

final class PhpFileEditor
{
    /**
     * @return list<Node>
     */
    public function parse(string $contents): array
    {
        return array_values((new ParserFactory)->createForNewestSupportedVersion()->parse($contents) ?? []);
    }

    /**
     * @param  list<Node>  $statements
     */
    public function print(array $statements): string
    {
        return (new Standard)->prettyPrintFile($statements).PHP_EOL;
    }
}

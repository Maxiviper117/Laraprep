<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Ast;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use RuntimeException;

final readonly class UserModelEditor
{
    public function __construct(
        private PhpFileEditor $phpFileEditor
    ) {}

    public function ensureMustVerifyEmail(string $contents): EditorResult
    {
        $statements = $this->phpFileEditor->parse($contents);
        $namespace = $this->findNamespace($statements);
        $namespaceStatements = $namespace instanceof Namespace_ ? array_values($namespace->stmts) : $statements;
        $class = $this->findClass($namespaceStatements);

        if (! $class instanceof Class_) {
            throw new RuntimeException('Could not locate a class in the user model.');
        }

        $changed = false;
        if ($this->ensureImport($namespace, $statements)) {
            $changed = true;
        }

        if ($this->ensureInterface($class)) {
            $changed = true;
        }

        if (! $changed) {
            return EditorResult::skipped('App\Models\User already implements MustVerifyEmail.');
        }

        return EditorResult::changed(
            $this->phpFileEditor->print($statements),
            'Updated App\Models\User to implement MustVerifyEmail.'
        );
    }

    /**
     * @param  list<Node>  $statements
     */
    private function findNamespace(array $statements): ?Namespace_
    {
        foreach ($statements as $statement) {
            if ($statement instanceof Namespace_) {
                return $statement;
            }
        }

        return null;
    }

    /**
     * @param  list<Node>  $statements
     */
    private function findClass(array $statements): ?Class_
    {
        foreach ($statements as $statement) {
            if ($statement instanceof Class_) {
                return $statement;
            }
        }

        return null;
    }

    /**
     * @param  list<Node>  $statements
     */
    private function ensureImport(?Namespace_ $namespace, array &$statements): bool
    {
        $targetStatements = $namespace instanceof Namespace_ ? array_values($namespace->stmts) : $statements;

        foreach ($targetStatements as $statement) {
            if (! $statement instanceof Use_) {
                continue;
            }

            foreach ($statement->uses as $use) {
                if ($use->name->toString() === 'Illuminate\Contracts\Auth\MustVerifyEmail') {
                    return false;
                }

                if (($use->alias?->toString() ?? $use->name->getLast()) === 'MustVerifyEmail') {
                    throw new RuntimeException('A conflicting MustVerifyEmail import already exists.');
                }
            }
        }

        $import = new Use_([new UseUse(new Name('Illuminate\Contracts\Auth\MustVerifyEmail'))]);

        if ($namespace instanceof Namespace_) {
            $namespaceStatements = array_values($namespace->stmts);
            array_splice($namespace->stmts, $this->firstClassOffset($namespaceStatements), 0, [$import]);

            return true;
        }

        array_splice($statements, $this->firstClassOffset($statements), 0, [$import]);

        return true;
    }

    private function ensureInterface(Class_ $class): bool
    {
        foreach ($class->implements as $interface) {
            if (in_array($interface->toString(), ['MustVerifyEmail', 'Illuminate\Contracts\Auth\MustVerifyEmail'], true)) {
                return false;
            }
        }

        $class->implements[] = new Name('MustVerifyEmail');

        return true;
    }

    /**
     * @param  list<Node>  $statements
     */
    private function firstClassOffset(array $statements): int
    {
        foreach ($statements as $index => $statement) {
            if ($statement instanceof Class_) {
                return $index;
            }
        }

        return count($statements);
    }
}

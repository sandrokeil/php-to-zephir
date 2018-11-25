<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace PhpToZephir\PhpParser\NodeVisitor;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;

class SortNamespaceClasses extends NodeVisitorAbstract
{
    private $namespaces = [];
    private $currentNamespace = '';

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_
            && ! isset($this->namespaces[$node->name->toString()])
        ) {
            $this->namespaces[$node->name->toString()] = [];
            $this->currentNamespace = $node->name->toString();
        }

        if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
            $this->namespaces[$this->currentNamespace][$node->namespacedName->toString()] = $node;

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function printSortedNamespaces(PrettyPrinterAbstract $printer): string
    {
        $code = '';

        foreach ($this->namespaces as $namespace => &$cls) {
            usort($cls, [self::class, 'sortNamespaces']);
            $namespaceNode = new Node\Stmt\Namespace_(new Node\Name($namespace), $cls);
            $code .= $printer->prettyPrint([$namespaceNode]) . PHP_EOL;
        }

        return $code;
    }

    public static function sortNamespaces(Node\Stmt\ClassLike $a, Node\Stmt\ClassLike $b): int
    {
        $aExtends = empty($a->extends);
        $bExtends = empty($b->extends);

        if ($aExtends && $bExtends) {
            return 0;
        }
        if ($aExtends && ! $bExtends) {
            return -1;
        }
        if (! $aExtends && $bExtends) {
            return 1;
        }
        $aNamespace = $a->namespacedName->toString();
        $bNamespace = $b->namespacedName->toString();

        if (! empty(array_filter($a->extends, function (Node\Name\FullyQualified $node) use ($bNamespace) {
            return $node->toString() === $bNamespace;
        }))) {
            return 1;
        }

        if (! empty(array_filter($b->extends, function (Node\Name\FullyQualified $node) use ($aNamespace) {
            return $node->toString() === $aNamespace;
        }))) {
            return -1;
        }

        return 0;
    }
}
